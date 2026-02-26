
pipeline {
    agent { label 'master' }   // Run only on the master node

    environment {
        // Email recipient
        EMAIL_TO = 'venkatesh.bandham@i95dev.com'
    }

    stages {

        stage('Sonar Scan') {
            steps {
                echo "Starting SonarQube Code Scan..."
                // Using your existing sonar-scanner path on master node
                sh "/var/lib/jenkins/tools/hudson.plugins.sonar.SonarRunnerInstallation/sonar-scanner-4.3/bin/sonar-scanner"
            }
        }

        /* 
        stage('Install Dependencies') {
            steps {
                withCredentials([file(credentialsId: 'composer_repo', variable: 'FILE')]) {
                    // Copy composer repo file (if required)
                    // sh 'cp $FILE ./'
                    sh "php81 /usr/bin/composer update --ignore-platform-reqs"
                    sh "php81 /usr/bin/composer install --ignore-platform-reqs"
                }
            }
        }

        stage('Magento Standards Check') {
            steps {
                // Run Magento coding standards check (optional)
                sh "vendor/bin/phpcs --standard=vendor/magento/magento-coding-standard/Magento2 --extensions=php --report-file=checkstyle.xml app/code/I95Dev -v || exit 0"
            }
        }
        */
    }

    post {
        success {
            emailext(
                to: "${EMAIL_TO}",
                subject: "Build SUCCESS in Jenkins: ${env.JOB_NAME} - #${env.BUILD_NUMBER}",
                body: '''SonarQube Scan completed successfully.

Check console output at ${BUILD_URL} to view results. 

--------------------------------------------------
${BUILD_LOG, maxLines=50, escapeHtml=false}
'''
            )
        }
        failure {
            emailext(
                to: "${EMAIL_TO}",
                subject: "Build FAILED in Jenkins: ${env.JOB_NAME} - #${env.BUILD_NUMBER}",
                body: '''SonarQube Scan failed!

Check console output at ${BUILD_URL} for details.

--------------------------------------------------
${BUILD_LOG, maxLines=50, escapeHtml=false}
'''
            )
        }
    }
}
