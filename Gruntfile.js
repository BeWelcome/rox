module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    less: {
      compileBeWelcome: {
        options: {
          strictMath: true,
          sourceMap: true,
          outputSourceFiles: true,
          sourceMapURL: 'bewelcome.css.map',
          sourceMapFilename: 'htdocs/styles/css/bewelcome.css.map'
        },
        files: {
          'htdocs/styles/css/bewelcome.css': 'htdocs/styles/less/bewelcome.less',
        }
      },
      minify: {
        options: {
          cleancss: true,
          report: 'min'
        },
        files: {
          'htdocs/styles/css/bewelcome.min.css': 'htdocs/styles/css/bewelcome.css',
        }
      }
    },
    watch: {
        files: "htdocs/styles/less/*",
        tasks: ["less"]
    } 
});

  // Load the plugin that provides the ('grunt-*') task.
  grunt.loadNpmTasks('grunt-recess');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-less');

  // Default task(s).
  grunt.registerTask('default', ['watch']);

};