module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    sass: {                              // Task
      compileBeWelcome: {                            // Target
        options: {                       // Target options
          style: 'expanded'
        },
        files: {                         // Dictionary of files
          'htdocs/styles/css/bewelcome.css': 'htdocs/styles/scss/bewelcome.scss'
        }
      }
    },
    copy: {
      jQuery: {
        files: [{
          expand: true,
          cwd: 'node_modules/jQuery/dist/',
          src: ['**'],
          dest: 'htdocs/js/jQuery/'
        }]
      },
      leafletjs: {
        files: [{
          expand: true,
          cwd: 'node_modules/leaflet/dist/',
          src: ['**', '!**/*.css', '!**/*.png'],
          dest: 'htdocs/js/leaflet/'
        }]
      },
      leafletcss: {
        files: [{
          expand: true,
          cwd: 'node_modules/leaflet/dist/',
          src: ['**', '!**/*.js'],
          dest: 'htdocs/css/leaflet/'
        }]
      },
      leafletimages: {
        files: [{
          expand: true,
          cwd: 'node_modules/leaflet/dist/images',
          src: ['**'],
          dest: 'htdocs/images/'
        }]
      },
      markerclusterjs: {
        files: [{
          expand: true,
          cwd: 'node_modules/leaflet.markercluster/dist/',
          src: ['**', '!**/*.css'],
          dest: 'htdocs/js/leaflet/'
        }]
      },
      markerclustercss: {
        files: [{
          expand: true,
          cwd: 'node_modules/leaflet.markercluster/dist/',
          src: ['**', '!**/*.js'],
          dest: 'htdocs/css/leaflet/'
        }]
      },
    },
  autoprefixer: {
      options: {
        browsers: [
          'Android 2.3',
          'Android >= 4',
          'Chrome >= 20',
          'Firefox >= 24', // Firefox 24 is the latest ESR
          'Explorer >= 9',
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
      }
    },
    
    csslint: {
      options: {
        csslintrc: 'htdocs/styles/scss/.csslintrc'
      },
      bewelcome: [
        'htdocs/styles/css/bewelcome.css'
      ]
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
      }
    },

    csscomb: {
      options: {
        config: 'htdocs/styles/scss/.csscomb.json'
      },
      bewelcome: {
        expand: true,
        cwd: 'htdocs/styles/css/',
        src: ['*.css', '!*.min.css'],
        dest: 'htdocs/styles/css/'
      }
    },
    watch: {
        dev: {
            files: "htdocs/styles/scss/*",
            tasks: ['sass:compileBeWelcome', 'cssmin:bewelcome']
        },
        dist: {
            files: "htdocs/styles/scss/*",
            tasks: ['sass:compileBeWelcome', 'autoprefixer:bewelcome', 'csscomb:bewelcome', 'cssmin:bewelcome' // , 'csslint:bewelcome'
            ]
        }
    } 
});

  // Load the plugin that provides the ('grunt-*') task.
  grunt.loadNpmTasks('grunt-recess');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-autoprefixer');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-csscomb');
  grunt.loadNpmTasks('grunt-contrib-csslint');
  

  // Default task for development (simply turn scss to css )
  grunt.registerTask('default', ['watch:dev']);

  // Distribution task
  grunt.registerTask('dist', ['watch:dist']);
  grunt.registerTask('copyfiles', ['copy:jQuery', 'copy:leafletjs', 'copy:leafletimages', 'copy:leafletcss', 'copy:markerclusterjs', 'copy:markerclustercss']);
};