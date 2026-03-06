import fs from 'node:fs';
import path from 'node:path';

const projectRoot = process.cwd();
const projectName = path.basename(projectRoot);
const distRoot = path.join(projectRoot, 'dist');
const outputDir = path.join(distRoot, projectName);

fs.rmSync(distRoot, { recursive: true, force: true });
fs.mkdirSync(outputDir, { recursive: true });

copyDirectory(projectRoot, outputDir, true);

console.log(`Created: ${outputDir}`);

function copyDirectory(sourceDir, targetDir, isRoot = false) {
	const entries = fs.readdirSync(sourceDir, { withFileTypes: true });

	for (const entry of entries) {
		const sourcePath = path.join(sourceDir, entry.name);
		const targetPath = path.join(targetDir, entry.name);

		if (entry.name.startsWith('.')) {
			continue;
		}

		if (entry.isDirectory()) {
			if (isRoot && entry.name === 'dist') {
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
