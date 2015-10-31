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
          'htdocs/styles/css/bewelcome.css': 'htdocs/styles/less/bewelcome.scss',
        }
      },
    },
    
  autoprefixer: {
      options: {
        browsers: [
          'Android 2.3',
          'Android >= 4',
          'Chrome >= 20',
          'Firefox >= 24', // Firefox 24 is the latest ESR
          'Explorer >= 8',
          'iOS >= 6',
          'Opera >= 12',
          'Safari >= 6'
        ]
      },
      bewelcome: {
        options: {
          map: true
        },
        src: 'htdocs/styles/css/bewelcome.css'
      },
    },
    
    csslint: {
      options: {
        csslintrc: 'htdocs/styles/less/.csslintrc'
      },
      bewelcome: [
        'htdocs/styles/css/bewelcome.css'
      ],
    },
    
    cssmin: {
      options: {
        compatibility: 'ie8',
        keepSpecialComments: '*',
        noAdvanced: true
      },
      bewelcome: {
        src: 'htdocs/styles/css/bewelcome.css',
        dest: 'htdocs/styles/css/bewelcome.min.css'
      },
    },

    csscomb: {
      options: {
        config: 'htdocs/styles/less/.csscomb.json'
      },
      bewelcome: {
        expand: true,
        cwd: 'htdocs/styles/css/',
        src: ['*.css', '!*.min.css'],
        dest: 'htdocs/styles/css/'
      },
    },
    watch: {
        dev: {
            files: "htdocs/styles/less/*",
            tasks: ['less:compileBeWelcome', 'cssmin:bewelcome']
        },
        dist: {
            files: "htdocs/styles/less/*",
            tasks: ['less:compileBeWelcome', 'autoprefixer:bewelcome', 'csscomb:bewelcome', 'cssmin:bewelcome', 'csslint:bewelcome']
        },
    } 
});

  // Load the plugin that provides the ('grunt-*') task.
  grunt.loadNpmTasks('grunt-recess');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-autoprefixer');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-csscomb');
  grunt.loadNpmTasks('grunt-contrib-csslint');
  

  // Default task for development (simply turn less to css )
  grunt.registerTask('default', ['watch:dev']);
  // Distribution task 
  grunt.registerTask('dist', ['watch:dist']);
};