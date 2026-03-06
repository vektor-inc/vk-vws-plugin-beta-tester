import fs from 'node:fs';
import path from 'node:path';

const projectRoot = process.cwd();
const packageJsonPath = path.join(projectRoot, 'package.json');
const packageName = getPackageName(packageJsonPath);
const projectName = packageName || path.basename(projectRoot);
const distRoot = path.join(projectRoot, 'dist');
const outputDir = path.join(distRoot, projectName);

fs.rmSync(distRoot, { recursive: true, force: true });
fs.mkdirSync(outputDir, { recursive: true });

copyDirectory(projectRoot, outputDir, true);

console.log(`Created: ${outputDir}`);

function getPackageName(packagePath) {
	if (!fs.existsSync(packagePath)) {
		return '';
	}

	try {
		const packageJson = JSON.parse(fs.readFileSync(packagePath, 'utf8'));
		return typeof packageJson.name === 'string' ? packageJson.name : '';
	} catch {
		return '';
	}
}

function copyDirectory(sourceDir, targetDir, isRoot = false) {
	const entries = fs.readdirSync(sourceDir, { withFileTypes: true });

	for (const entry of entries) {
		const sourcePath = path.join(sourceDir, entry.name);
		const targetPath = path.join(targetDir, entry.name);

		if (entry.name.startsWith('.')) {
			continue;
		}

		if (entry.isDirectory()) {
			if (isRoot && (entry.name === 'dist' || entry.name === 'node_modules')) {
				continue;
			}

			fs.mkdirSync(targetPath, { recursive: true });
			copyDirectory(sourcePath, targetPath);
			continue;
		}

		if (entry.isFile()) {
			if (isRoot && entry.name === 'package.json') {
				continue;
			}

			fs.copyFileSync(sourcePath, targetPath);
		}
	}
}
