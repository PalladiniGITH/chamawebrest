# chamawebrest

## Kubernetes

A Kubernetes manifest is available at `k8s/web-deployment.yaml`. It creates a
Deployment and a Service that mirror the Docker Compose setup and expose
container port `80`.

### Running on Minikube

1. Build the image inside the Minikube Docker daemon and apply the manifest:

   ```bash
   eval $(minikube -p minikube docker-env)
   docker build -t chamawebrest:latest .
   kubectl apply -f k8s/web-deployment.yaml
   ```

2. Access the web portal using one of the methods below:

   * **NodePort:** start Minikube allowing access on `localhost:8080` and open
     the service:

     ```bash
     minikube start --ports=8080:30080
     # after the pods are running
     http://localhost:8080
     ```

   * **Port forward:** if you prefer not to use a NodePort, run:

     ```bash
     kubectl port-forward svc/web 8080:80
     ```

     then browse to `http://localhost:8080`.
