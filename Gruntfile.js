module.exports = function(grunt) {

  var jsPath  = '';
  var cssPath = '';

  // Configure Grunt
  grunt.initConfig({

    // base configure
    pkg: grunt.file.readJSON('package.json'),

    // SASS
    sass: {
      dist: {
        options: {
          style: 'compressed'
        },
        files: {
          'public_html/css/main.css' : 'public_html/css/main.scss'
        }
      }
    },

    // js concat
    concat: {
      dist: {
        src: [
          'public_html/includes/js/libs/jquery*.js',
          'public_html/includes/js/libs/bootstrap.js',
          'public_html/includes/js/refactored/*.js',
        ],
        dest: 'public_html/includes/js/build/production.js',
      }
    },

    // js minifiy
    uglify: {
      build: {
        src: 'public_html/includes/js/build/production.js',
        dest: 'public_html/includes/js/build/production.min.js'
      }
    },

    // watch the files for changes
    watch: {
      options: {
        livereload: true,
      },
      scripts: {
        files: ['public_html/includes/js/refactored/*.js'],
        tasks: ['concat', 'uglify'],
      },
      css: {
        files: ['public_html/css/*.scss'],
        tasks: ['sass'],
      },
    },

  });

  require('load-grunt-tasks')(grunt);

  // Default Task is basically a rebuild
  grunt.registerTask('default', ['concat', 'uglify', 'sass', 'watch']);

};