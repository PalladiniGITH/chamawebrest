# chamawebrest

## Kubernetes deployment

A basic manifest is provided at `k8s/gateway-deployment.yaml` to run the application in a cluster. The service is exposed as a NodePort on **30080**. After applying the manifest you can reach the gateway at `http://<node-ip>:30080`.

```bash
kubectl apply -f k8s/gateway-deployment.yaml
```

