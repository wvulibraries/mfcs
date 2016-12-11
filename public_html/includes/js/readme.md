# JavaScript Tooling for Deployment

For organization and maintainability the files have been broken up, but for deployment are concatenated together for performance.  Also for performance reasons the files have been broken up by job specific / page specifics within the application.  

## Changing JS

Modify JS files only in the develop folder.  The build files will be built using grunt.  

## GRUNT / NODE

Compiling NPM / Node Modules needed to run grunt.  

 * ```cd mfcs``` -- Change directory to MFCS Main Directory
 * ```npm install``` -- installs all the npm files in the ```package.json```

Running Grunt to Make Changes to the JS

 * ```cd mfcs``` -- Change directory to MFCS Main Directory
 * ``` grunt ``` -- Runs grunt does tasks in ```Gruntfile.js```

## SCSS

Running grunt will also compile and manipulate your SCSS.  You can setup and add other libraries using NPM to modify SCSS you just have to update the Gruntfile.js to add those into the build.  May want to look at something like Autoprefixer and linting for getting cleaner SCSS.  Looking at breaking the SCSS into more library like items and keeping it dryer might be good as well.  

## TODO

* Update Minification Settings for JS
* Performance Tooling and Testing
* Modularize the UI using something like Webpack or RequireJS
