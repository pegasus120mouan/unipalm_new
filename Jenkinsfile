pipeline{
  agent any
  environment {
    staging_server="51.178.44.177"
  }

  stages{
      stage("Deploiement vers Production"){
          steps{
            sh 'scp ${WORKSPACE}/* root@${staging_server}:/var/www/html/ovl/'
          }
      }

  }




}