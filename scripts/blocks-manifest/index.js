import fs from 'fs';
import { join } from 'path';
import { execFileSync } from 'child_process';

/**
 * Custom plugin to move blocks-manifest.php to dist/assets/blocks/
 */
class blocksManifestPlugin {
	apply(compiler) {
		compiler.hooks.done.tap('BlocksManifestPlugin', (stats) => {
			// Prevent execution on incremental watch re-compiles
			if (compiler.watchMode) {
				return;
			}

			const outputPath = compiler.options.output.path;
			
			const sourceFile = join(outputPath, 'blocks-manifest.php');
			const targetDir = join(outputPath, 'assets/blocks');
			const targetFile = join(targetDir, 'blocks-manifest.php');

			console.log('BlocksManifestPlugin: Running...');

			// Ensure output directory exists (dist/)
			if (!fs.existsSync(outputPath)) {
				fs.mkdirSync(outputPath, { recursive: true });
			}

			// Generate the manifest using wp-scripts
			try {
				console.log(`BlocksManifestPlugin: Generating manifest at ${sourceFile}...`);

				const npxExecutable = process.platform === 'win32' ? 'npx.cmd' : 'npx';
				
				execFileSync(
					npxExecutable, 
					[
						'wp-scripts', 
						'build-blocks-manifest', 
						'--input=src', 
						`--output=${sourceFile}`
					], 
					{ stdio: 'inherit' }
				);
			} catch (error) {
				console.error('BlocksManifestPlugin: Failed to generate manifest:', error.message);
			}

			// Move it to the target directory
			if (fs.existsSync(sourceFile)) {
				if (!fs.existsSync(targetDir)) {
					fs.mkdirSync(targetDir, { recursive: true });
				}
				
				try {
					fs.copyFileSync(sourceFile, targetFile);
					fs.unlinkSync(sourceFile);
					console.log('BlocksManifestPlugin: blocks-manifest.php moved to dist/assets/blocks/');
				} catch (err) {
					console.error('BlocksManifestPlugin: Error moving manifest:', err.message);
				}
			} else {
				console.warn('BlocksManifestPlugin: blocks-manifest.php was not found at expected source path:', sourceFile);
			}
		});
	}
}

export default blocksManifestPlugin;
