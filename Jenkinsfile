pipeline {
    agent any

    environment {
        GHCR_IMAGE = "ghcr.io/atuan0354/student-management"
        HELM_REPO  = "https://github.com/atuan0354/student-helm.git"
        HELM_PATH  = "student-app/values.yaml"
    }

    stages {
        stage("Checkout Source") {
            steps {
                checkout scm
            }
        }

        stage("Build Docker Image") {
            steps {
                script {
                    // build number làm tag
                    env.IMAGE_TAG = "${BUILD_NUMBER}"
                }
                sh """
                    docker build -t ${GHCR_IMAGE}:${IMAGE_TAG} .
                """
            }
        }

        stage("Login & Push GHCR") {
            steps {
                withCredentials([usernamePassword(credentialsId: 'ghcr-creds',
                                                 usernameVariable: 'GHCR_USER',
                                                 passwordVariable: 'GHCR_PASS')]) {
                    sh """
                        echo \$GHCR_PASS | docker login ghcr.io -u \$GHCR_USER --password-stdin
                        docker push ${GHCR_IMAGE}:${IMAGE_TAG}
                    """
                }
            }
        }

        stage("Update Helm Values & Push") {
            steps {
                dir("helm-workdir") {
                    deleteDir() 
                    withCredentials([usernamePassword(credentialsId: 'github-creds',
                                                     usernameVariable: 'GIT_USER',
                                                     passwordVariable: 'GIT_PASS')]) {
                        sh """
                            git clone ${HELM_REPO} .
                            sed -i 's/^  tag: .*/  tag: "${IMAGE_TAG}"/' ${HELM_PATH}

                            git config user.email "atuan0354@gmail.com"
                            git config user.name  "Jenkins CI"

                            git add ${HELM_PATH}
                            git commit -m "update image tag to ${IMAGE_TAG}" || echo "No changes to commit"

                            git remote set-url origin https://\$GIT_USER:\$GIT_PASS@github.com/atuan0354/student-helm.git
                            git push origin main
                        """
                    }
                }
            }
        }
    }
}
