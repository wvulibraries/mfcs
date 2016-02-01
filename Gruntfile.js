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

    // // js concat
    // concat: {
    //   dist: {
    //     src: [
    //       'public_html/includes/js/libs/jquery*.js',
    //       'public_html/includes/js/libs/bootstrap.js',
    //       'public_html/includes/js/refactored/*.js',
    //     ],
    //     dest: 'public_html/includes/js/build/production.js',
    //   }
    // },

    // js concat
    concat: {
      dist: {
        files:[
            // Creates all the public libraries jquery/bootstrap/etc
            { src: [
                'public_html/includes/js/libs/jquery*.js',
                'public_html/includes/js/libs/bootstrap.js',
              ],
              dest: 'public_html/includes/js/build/libs/libs.js',
            },
            // CreatesForm JS
            { src:[
                'public_html/includes/js/refactored/createForm_form.js',
                'public_html/includes/js/refactored/createForm_formPermissions.js',
                'public_html/includes/js/refactored/createForm_NavigationCreator.js',
                'public_html/includes/js/refactored/fineUploaderInit.js'
              ],
              dest: 'public_html/includes/js/build/formBuilder/formBuilder.js',
            },
            // App Wide JS
            { src:[
                'public_html/includes/js/refactored/currentProjects.js',
                'public_html/includes/js/refactored/filePreview.js',
                'public_html/includes/js/refactored/commonAppJS.js'
              ],
              dest: 'public_html/includes/js/build/common/main.js'
            },
            // Object Creator JS
            { src:[
                'public_html/includes/js/refactored/object_metadata.js',
                'public_html/includes/js/refactored/object_dataEntry.js',
                'public_html/includes/js/refactored/selectForm_dataEntry.js',
                'public_html/includes/js/refactored/fineUploaderInit.js'
              ],
              dest: 'public_html/includes/js/build/object/object.js'
            },
            // Revisions Page
            { src:[
                'public_html/includes/js/refactored/revisions.js',
              ],
              dest: 'public_html/includes/js/build/revision/revisions.js'
            }
        ]
      }
    },

    // js minifiy
    uglify: {
      build: {
        // files:[
        //     { // Creat min libs
        //       src: 'public_html/includes/js/build/*/*.js',
        //       dest: 'public_html/includes/js/build/*/*.min.js'
        //     }
        // ],
        //
        // files: [
        //   {
        //     expand: true,     // Enable dynamic expansion.
        //     cwd: 'public_html/includes/js/build', // Src matches are relative to this path.
        //     src: ['**/*.js'], // Actual pattern(s) to match.
        //     dest: 'public_html/includes/js/build',   // Destination path prefix.
        //     ext: '.min.js',   // Dest filepaths will have this extension.
        //     extDot: 'first'   // Extensions in filenames begin after the first dot
        //   },
        // ],
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