pipeline {
  agent any

  environment {
    REGISTRY = "ghcr.io"
    IMAGE_NAME = "atuan0354/student-management"
    HELM_REPO = "https://github.com/atuan0354/student-helm.git"
    HELM_PATH = "student-app"
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
          env.TAG = "${BUILD_NUMBER}"
          sh """
            docker build -t ${REGISTRY}/${IMAGE_NAME}:${TAG} .
          """
        }
      }
    }

    stage("Login & Push GHCR") {
      steps {
        withCredentials([string(credentialsId: 'ghcr_token', variable: 'GHCR_TOKEN')]) {
          sh """
            echo $GHCR_TOKEN | docker login ${REGISTRY} -u atuan0354 --password-stdin
            docker push ${REGISTRY}/${IMAGE_NAME}:${TAG}
          """
        }
      }
    }

    stage("Update Helm Values & Push") {
      steps {
        withCredentials([string(credentialsId: 'git_token', variable: 'GIT_TOKEN')]) {
          sh """
            rm -rf helmrepo
            git clone https://atuan0354:$GIT_TOKEN@github.com/atuan0354/student-helm.git helmrepo
            cd helmrepo/${HELM_PATH}

            sed -i "s/tag: .*/tag: \\"${TAG}\\"/g" values.yaml

            git config user.email "ci@jenkins.local"
            git config user.name "Jenkins CI"
            git add values.yaml
            git commit -m "update image tag to ${TAG}" || true
            git push origin main
          """
        }
      }
    }
  }
}
