module.exports = function(grunt) {
  grunt.initConfig({
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
        files:[
            // Libs
            { src: [
                'public_html/includes/js/libs/jquery*.js',
                'public_html/includes/js/libs/bootstrap.js',
              ],
              dest: 'public_html/includes/js/build/libs/libs.js',
            },
            // CreatesForm JS
            { src:[
                'public_html/includes/js/develop/createForm_form.js',
                'public_html/includes/js/develop/createForm_formPermissions.js',
                'public_html/includes/js/develop/createForm_NavigationCreator.js',
                'public_html/includes/js/develop/fineUploaderInit.js'
              ],
              dest: 'public_html/includes/js/build/formBuilder/formBuilder.js',
            },
            // App Wide JS
            { src:[
                'public_html/includes/js/develop/currentProjects.js',
                'public_html/includes/js/develop/filePreview.js',
                'public_html/includes/js/develop/commonAppJS.js',
              ],
              dest: 'public_html/includes/js/build/common/main.js'
            },
             // DataEntry/View/Etc
            { src:[
                'public_html/includes/js/develop/obect_metadata.js',
                'public_html/includes/js/develop/object_dataEntry.js',
                'public_html/includes/js/develop/selectForm_dataEntry.js',
                'public_html/includes/js/develop/fineUploaderInit.js'
              ],
              dest: 'public_html/includes/js/build/object/object.js'
            },
            // Batch Upload
            { src:[
                'public_html/includes/js/develop/batchUpload.js',
                'public_html/includes/js/develop/helperFunctions.js',
              ],
              dest: 'public_html/includes/js/build/batchUpload/batchUpload.js'
            },
            // Revisions Page
            { src:[
                'public_html/includes/js/develop/revisions.js',
              ],
              dest: 'public_html/includes/js/build/revision/revisions.js'
            },
            // Batch Upload
            { src:[
                'public_html/includes/js/develop/moveObjects.js',
                'public_html/includes/js/develop/helperFunctions.js',
              ],
              dest: 'public_html/includes/js/build/batchUpload/moveObjects.js'
            },
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
        files: ['public_html/includes/js/develop/*.js'],
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