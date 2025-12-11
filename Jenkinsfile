#!groovy

def FAILED_STAGE

pipeline {
  agent none

  environment {
    REPO_NAME = "braintree-php"
    SLACK_CHANNEL = "#auto-team-sdk-builds"
  }

  options {
    buildDiscarder(logRotator(numToKeepStr: '50'))
    timestamps()
    timeout(time: 120, unit: 'MINUTES')
  }

  stages {
    stage("SDK Tests") {
      when {
        branch 'master'
      }

      parallel {
        stage("PHP 7.4.0 Buster") {
          agent {
            node {
              label ""
              customWorkspace "workspace/${REPO_NAME}"
            }
          }

          steps {
            build job: 'php_7.4.0-buster_server_sdk_master', wait: true
          }

          post {
            failure {
              script {
                FAILED_STAGE = env.STAGE_NAME
              }
            }
          }
        }

        stage("PHP 8.2.0 Bullseye") {
          agent {
            node {
              label ""
              customWorkspace "workspace/${REPO_NAME}"
            }
          }

          steps {
            build job: 'php_8.2.0-bullseye_server_sdk_master', wait: true
          }

          post {
            failure {
              script {
                FAILED_STAGE = env.STAGE_NAME
              }
            }
          }
        }
      }
    }
  }

  post {
    unsuccessful {
      slackSend color: "danger",
        channel: "${env.SLACK_CHANNEL}",
        message: "${env.JOB_NAME} - #${env.BUILD_NUMBER} Failure after ${currentBuild.durationString} at stage \"${FAILED_STAGE}\"(<${env.BUILD_URL}|Open>)"
    }
  }
}
