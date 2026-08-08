const fs = require( 'fs' );
const { join } = require( 'path' );
const { execFileSync } = require( 'child_process' );

/**
 * Custom plugin to create blocks-manifest.php in dist
 * Then move it to dist/assets/blocks/
 */
class blocksManifestPlugin {
	apply( compiler ) {
		compiler.hooks.done.tap( 'BlocksManifestPlugin', ( stats ) => {
			const outputPath = compiler.options.output.path;

			const sourceFile = join( outputPath, 'blocks-manifest.php' );
			const targetDir = join( outputPath, 'assets/blocks' );
			const targetFile = join( targetDir, 'blocks-manifest.php' );

			console.log( 'BlocksManifestPlugin: Running...' );

			// Ensure output directory exists (dist/)
			if ( ! fs.existsSync( outputPath ) ) {
				fs.mkdirSync( outputPath, { recursive: true } );
			}

			// Generate the manifest using wp-scripts
			try {
				console.log(
					`BlocksManifestPlugin: Generating manifest at ${ sourceFile }...`
				);

				const npxExecutable =
					process.platform === 'win32' ? 'npx.cmd' : 'npx';

				execFileSync(
					npxExecutable,
					[
						'wp-scripts',
						'build-blocks-manifest',
						'--input=src',
						`--output=${ sourceFile }`,
					],
					{ stdio: 'inherit' }
				);
			} catch ( error ) {
				console.error(
					'BlocksManifestPlugin: \x1b[1;31mFailed to generate manifest:\x1b[0m',
					error.message
				);
			}

			// Move it to the target directory
			if ( fs.existsSync( sourceFile ) ) {
				if ( ! fs.existsSync( targetDir ) ) {
					fs.mkdirSync( targetDir, { recursive: true } );
				}

				try {
					fs.copyFileSync( sourceFile, targetFile );
					fs.unlinkSync( sourceFile );
					console.log(
						'BlocksManifestPlugin: \x1b[1;32mblocks-manifest.php moved to dist/assets/blocks/\x1b[0m'
					);
				} catch ( err ) {
					console.error(
						'BlocksManifestPlugin: \x1b[1;31mError moving manifest:\x1b[0m',
						err.message
					);
				}
			} else {
				console.warn(
					'BlocksManifestPlugin: \x1b[1;31mblocks-manifest.php was not found at expected source path:\x1b[0m',
					sourceFile
				);
			}
		} );
	}
}

module.exports = blocksManifestPlugin;
