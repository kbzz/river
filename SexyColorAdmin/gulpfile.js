var gulp = require('gulp'),
	concat = require('gulp-concat'),
	fileinclude = require('gulp-file-include'),
	sass = require('gulp-ruby-sass'),
	browserSync = require('browser-sync').create();

var pagePath = 'src/pages/**/*.html';

gulp.task('fileinclude', function() {
	// 适配pages中所有文件夹下的所有html，排除template下的include文件夹中html
	gulp.src([pagePath, '!src/pages/template/**/*.html'])
		.pipe(fileinclude({
			prefix: '@@',
			basepath: '@file'
		}))
		.pipe(gulp.dest('dist/pages'));
});

gulp.task('browser-sync', function() {
	browserSync.init({
		server: {
			baseDir: './'
		},
		startPath: './dist/pages/index.html'
	});
});

var compileSASS = function(fileName, options) {
	return sass('./src/scss/**/*.scss', options)
		.pipe(concat(fileName))
		.pipe(gulp.dest('dist/build/css'))
		.pipe(browserSync.stream())
		.on('error', sass.logError);
};

gulp.task('sass', function() {
	return compileSASS('custom-extend.css', {style:'compressed'});
});


gulp.task('watch', function() {
	gulp.watch(pagePath, ['fileinclude', browserSync.reload]);
	gulp.watch('src/scss/*.scss', ['sass']);
});

gulp.task('watch2', function() {
	gulp.watch(pagePath, ['fileinclude']);
	gulp.watch('src/scss/*.scss', ['sass']);
});

//Default Task
// gulp.task('default', ['fileinclude', 'browser-sync', 'watch']);
gulp.task('default', ['fileinclude', 'watch2']);