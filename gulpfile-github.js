/** 
 * Special file for github test 
 * Meant to use build task on github action
 * Doesn't include an import of an .env that breaks github action (conditional import is not allowed)
 */

import gulp from 'gulp';
import plumber from 'gulp-plumber';
import notify from 'gulp-notify';
import longerif from 'gulp-if';
import replace from 'gulp-replace';
import cleanCss from 'gulp-clean-css';
import changed from 'gulp-changed';
import rename from 'gulp-rename';
import terser from 'gulp-terser';
import imagemin from 'gulp-imagemin';
import fs from 'fs-extra';
import nodeNotifier from 'node-notifier';

var errorHandler = function(error) {				/* handle and display errors with notify */
	notify.onError({
		title: '[' + error.plugin + '] Task Failed',
		message: error.message,
		icon: ext_cred.base.gulpimg,
		sound: true
	})(error);
	console.log("Error:", error.toString()); 
	/* this.emit('end'); removed, now called in plumber */
};

/* Copied/watched files */
var paths = {
	base: {
		src: './src',					/* main lumiere path source */
		dist: './dist',					/* main lumiere path destination */
	},
	stylesheets: {
		src: [
			'./src/**/*.css',
			'!./src/assets/js/highslide/*.*',
			'!./src/vendor/**/*.*'
		],
		dist: './dist'
	},
	javascripts: {
		src: [ 
			'./src/**/*.js',
				'!./src/assets/js/highslide/**/**/*.*',
				'!./src/vendor/**/*.*'
		],
		dist: './dist'
	},
	images: {
		src: [
			'./src/.**/*.{jpg,jpeg,gif,png}', 				/* extra pics for .wordpress.org */
			'./src/**/*.{jpg,jpeg,gif,png}',
			'!./src/vendor/**/*.*'
	],
		dist: './dist'
	},
	files: {
		src: [	'./src/**/*.{php,html,htm,ico,webmanifest,md,txt,json}', 
			'./src/vendor/twbs/bootstrap/dist/**/*.{min.js,min.css}', 

			/* Remove irrelevant files in src/vendor */ 
			'!./src/vendor/bin/*.*',				
			'!./src/vendor/duck7000/imdb-graphql-php/src/Psr/**/*.*',
			'!./src/vendor/duck7000/imdb-graphql-php/doc/**/*.*',
			'!./src/vendor/twbs/bootstrap/build/**/*.*',
			'!./src/vendor/twbs/bootstrap/js/**/*.*',
			'!./src/vendor/twbs/bootstrap/nuget/**/*.*',
			'!./src/vendor/twbs/bootstrap/scss/**/*.*',
			'!./src/vendor/twbs/bootstrap/site/**/*.*',
			'!./src/vendor/twbs/bootstrap/.github/**/*.*',
			'./src/**/*.+(psd)', 
			'./src/.**/**/*.{psd,json}',	 				/* extra files for .wordpress.org */
			'./src/languages/*.*',
				'!./src/languages/*.temp.po',  
			'./src/assets/js/highslide/**/**/*.*'
		],
		dist: './dist'
	}
};

// Task 1 - Minify CSS
gulp.task('stylesheets', () => {

	return gulp
		.src( paths.stylesheets.src , {base: paths.base.src } )
		.pipe(plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		.pipe(rename({suffix: '.min'}))
		.pipe(changed( paths.stylesheets.dist ))
		// Removed class .dropdown-menu in CSS bootstrap.css which breaks OCEANWP
		.pipe(longerif( (file) => file.path.match('bootstrap.min.css'), replace(/(\.dropdown-menu\s\{).+?(border-radius: 0\.25rem;\s\})/s, '')) )
		.pipe(cleanCss({debug: true}, (details) => {
			console.log(`${details.name}: ${details.stats.originalSize}`);
			console.log(`${details.name}: ${details.stats.minifiedSize}`);
		}))
		.pipe(gulp.dest( paths.stylesheets.dist ))
});

// Task 2 - Minify JS
gulp.task('javascripts', () => {
	return gulp
		.src( paths.javascripts.src , {base: paths.base.src } )
		.pipe(plumber( function (err) { errorHandler(err) })) /* throws a popup & console error msg */
		.pipe(rename({suffix: '.min'}))
		.pipe(changed( paths.javascripts.dist ))
		.pipe(terser())
		.pipe(gulp.dest( paths.javascripts.dist ))
});


// Task 3 - Compress images -> jpg can't be compressed, selecting png and gif only
gulp.task('images', () => {
	return gulp
		.src( paths.images.src, {base: paths.base.src, encoding: false } )
		.pipe(plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		.pipe(changed( paths.images.dist ))
		.pipe(imagemin())
		.pipe(gulp.dest( paths.images.dist ))
});

// Task 4 - Transfer untouched files -> jpg can't be compressed, transfered here
gulp.task('files_copy', () => {

	return gulp
		.src( paths.files.src, {base: paths.base.src, encoding: false } )
		.pipe(plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		.pipe(changed( paths.files.dist ))
		.pipe(gulp.dest( paths.files.dist ))
		.on("error", function (err) { errorHandler(err); console.log("Error:", err); }) /* old way, but maybe need to get an actual ssh error msg? */
});

// Task 7 - Build all files
gulp.task('build', (cb) => {
	gulp.series( 'javascripts')( cb );
	gulp.series( 'stylesheets')( cb );
	gulp.series( 'images')( cb );
	gulp.series( 'files_copy')( cb );
});

// Task 8 - Default
gulp.task('default', () => {
	gulp.series( 'build' );
});

