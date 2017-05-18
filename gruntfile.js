'use strict';
module.exports = function (grunt) {

	// load all grunt tasks
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),
		
		// Gulp can't do what I need well enough
		compress: {
			main: {
				options: {
					archive: 'gscr-cpt-radio-shows.zip'
				},
				files: [
					{expand: true, dot: true, src: ['./gscr-cpt-radio-shows/**/*.*'], dest: './'}
				]
			}
		}

	});
	
};