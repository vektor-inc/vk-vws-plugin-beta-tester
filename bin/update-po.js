#!/usr/bin/env node
'use strict';

const fs = require('fs');
const path = require('path');
const encoding = require('encoding');

const [, , poPathArg, potPathArg] = process.argv;

if (!poPathArg || !potPathArg) {
	console.error('Usage: node bin/update-po.js <po-file> <pot-file>');
	process.exit(1);
}

const poPath = path.resolve(process.cwd(), poPathArg);
const potPath = path.resolve(process.cwd(), potPathArg);

const ensureFile = (targetPath) => {
	if (!fs.existsSync(targetPath)) {
		console.error(`File not found: ${targetPath}`);
		process.exit(1);
	}
	return fs.readFileSync(targetPath);
};

const HEADER_CHARSET_REGEX = /[; ]charset\s*=\s*([\w-]+)/i;
const PREVIOUS_OBSOLETE_REGEX = /^#~\|.*$/gm;

const getHeaderValue = (headers, key) => {
	if (!headers) {
		return undefined;
	}
	const direct = headers[key];
	if (typeof direct !== 'undefined') {
		return direct;
	}
	const lowerKey = key.toLowerCase();
	for (const headerKey of Object.keys(headers)) {
		if (headerKey.toLowerCase() === lowerKey) {
			return headers[headerKey];
		}
	}
	return undefined;
};

const parsePluralCount = (headerSources) => {
	for (const headers of headerSources) {
		const pluralForms = getHeaderValue(headers, 'plural-forms');
		if (typeof pluralForms === 'string') {
			const match = pluralForms.match(/nplurals\s*=\s*(\d+)/i);
			if (match) {
				const parsed = parseInt(match[1], 10);
				if (!Number.isNaN(parsed) && parsed > 0) {
					return parsed;
				}
			}
		}
	}
	return undefined;
};

const detectCharset = (buffer) => {
	const headerSample = buffer.slice(0, 2048).toString('ascii');
	const match = headerSample.match(HEADER_CHARSET_REGEX);
	return match ? match[1].toLowerCase() : 'utf-8';
};

const stripPreviousReferences = (buffer) => {
	const charset = detectCharset(buffer);
	let utf8Content;

	if (charset === 'utf-8' || charset === 'utf8') {
		utf8Content = buffer.toString('utf8');
	} else {
		utf8Content = encoding.convert(buffer, 'utf-8', charset).toString('utf8');
	}

	const sanitized = utf8Content.replace(PREVIOUS_OBSOLETE_REGEX, '');

	if (charset === 'utf-8' || charset === 'utf8') {
		return Buffer.from(sanitized, 'utf8');
	}

	return Buffer.from(encoding.convert(Buffer.from(sanitized, 'utf8'), charset));
};

const run = async () => {
	const gettextModule = await import('gettext-parser');
	const gettextPo = gettextModule.po || (gettextModule.default && gettextModule.default.po);

	if (!gettextPo) {
		console.error('Failed to load gettext-parser PO utilities.');
		process.exit(1);
	}

	const parseCatalog = (targetPath, options = {}) => {
		const readBuffer = ensureFile(targetPath);
		try {
			return gettextPo.parse(readBuffer, options);
		} catch (error) {
			if (error && /Invalid key name "\|"/.test(error.message || '')) {
				return gettextPo.parse(stripPreviousReferences(readBuffer), options);
			}
			throw error;
		}
	};

	const pot = parseCatalog(potPath);
	const po = parseCatalog(poPath);

	const merged = {
		charset: pot.charset || po.charset,
		headers: { ...po.headers },
		translations: {}
	};

	if (pot.headers && pot.headers['pot-creation-date']) {
		merged.headers['pot-creation-date'] = pot.headers['pot-creation-date'];
	}
	if (!getHeaderValue(merged.headers, 'plural-forms') && pot.headers) {
		const pluralFormsFromPot = getHeaderValue(pot.headers, 'plural-forms');
		if (pluralFormsFromPot) {
			merged.headers['plural-forms'] = pluralFormsFromPot;
		}
	}

	const pluralCount = parsePluralCount([po.headers, pot.headers, merged.headers]);

	const mergeEntry = (potEntry, poEntry) => {
		const baseEntry = JSON.parse(JSON.stringify(potEntry));
		const pluralLength = baseEntry.msgid_plural
			? pluralCount || Math.max(baseEntry.msgstr.length || 0, poEntry && Array.isArray(poEntry.msgstr) ? poEntry.msgstr.length : 0, 1)
			: Math.max(baseEntry.msgstr.length || 0, 1);
		const msgstr = new Array(pluralLength).fill('');

		if (poEntry && Array.isArray(poEntry.msgstr)) {
			poEntry.msgstr.slice(0, pluralLength).forEach((value, index) => {
				msgstr[index] = value;
			});
		}

		baseEntry.msgstr = msgstr;

		if (poEntry && poEntry.comments) {
			baseEntry.comments = { ...baseEntry.comments, ...poEntry.comments };
		}

		return baseEntry;
	};

	const contexts = new Set([
		...Object.keys(pot.translations || {}),
		...Object.keys(po.translations || {})
	]);

	contexts.forEach((context) => {
		const potContext = pot.translations[context] || {};
		const poContext = po.translations[context] || {};
		const mergedContext = {};

		if (poContext['']) {
			mergedContext[''] = JSON.parse(JSON.stringify(poContext['']));
		} else if (potContext['']) {
			mergedContext[''] = JSON.parse(JSON.stringify(potContext['']));
		}

		Object.keys(potContext).forEach((msgid) => {
			if (msgid === '') {
				return;
			}
			mergedContext[msgid] = mergeEntry(potContext[msgid], poContext[msgid]);
		});

		Object.keys(poContext).forEach((msgid) => {
			if (msgid === '' || mergedContext[msgid]) {
				return;
			}
			const obsoleteEntry = JSON.parse(JSON.stringify(poContext[msgid]));
			obsoleteEntry.obsolete = true;
			mergedContext[msgid] = obsoleteEntry;
		});

		if (Object.keys(mergedContext).length > 0) {
			merged.translations[context] = mergedContext;
		}
	});

	const output = gettextPo.compile(merged, { sort: true });

	fs.writeFileSync(poPath, output);

	console.log(`Updated ${poPathArg} using ${potPathArg}`);
};

run().catch((error) => {
	console.error(error);
	process.exit(1);
});
