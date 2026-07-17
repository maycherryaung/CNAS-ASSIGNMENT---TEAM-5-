# VMware Ubuntu kubeadm Setup Using Your School Lab VMs

This guide matches the Week 11 and Week 12 CNAS practical setup you completed.

## Lab VMs confirmed from the practical slides

| VM | Role | IP address | Hostname |
| --- | --- | --- | --- |
| Master/control-plane | Kubernetes master | `192.168.2.21` | `vm-master` |
| Worker 1 | Kubernetes worker | `192.168.2.22` | `vm-childnode1` |
| Worker 2 | Kubernetes worker | `192.168.2.23` | `vm-childnode2` |

This is suitable for the assignment because it gives you one master node and two worker nodes, which meets the assignment requirement for a 2-3 worker-node Kubernetes cluster.

## What your completed labs already cover

From Week 11:

- VMware Ubuntu nodes
- 1 master node and 2 child/worker nodes
- Swap disabled
- Hostname and `/etc/hosts` setup
- Docker installed on each node
- containerd configured
- kubeadm, kubelet, and kubectl installed
- Kubernetes cluster initialized using kubeadm
- Flannel pod network installed
- Worker nodes joined to the master
- NGINX Deployment and Service tested
- NodePort access tested
- Pod self-healing tested by deleting a pod

From Week 12:

- SecurityContext
- Pod Security Admission
- RBAC authorization
- NGINX Ingress Controller
- TLS with self-signed certificate
- Prometheus and Grafana monitoring

## Quick suitability verdict

Yes, these 3 VMs are usable for the assignment.

They already cover the hardest part: a real multi-node Kubernetes cluster. The remaining work is to deploy this assignment's PHP-MySQL application and add the assignment-specific artifacts:

- Docker image for the PHP app
- MySQL StatefulSet and persistent storage
- Ingress rule for `cnas.local`
- HPA and metrics-server
- PodDisruptionBudget
- NetworkPolicy
- Monitoring evidence
- CI/CD evidence
- Report screenshots

## Check the existing cluster before deployment

Run on `vm-master`:

```bash
kubectl get nodes -o wide
kubectl cluster-info
kubectl get pods -A
```

Expected result:

- `vm-master` is Ready
- `vm-childnode1` is Ready
- `vm-childnode2` is Ready
- Flannel pods are Running
- CoreDNS pods are Running

> Screenshot to capture: `kubectl get nodes -o wide`.

## Check whether dynamic storage exists

kubeadm clusters usually do not include a default StorageClass. Check:

```bash
kubectl get storageclass
```

If no default StorageClass exists, use the local storage manifest already added in this project:

```text
k8s/local-storage.yaml
```

It creates:

- StorageClass: `cnas-local-storage`
- PersistentVolume: `cnas-mysql-pv`
- Local path: `/mnt/cnas-mysql`
- Node: `vm-childnode1`

Prepare the folder on `vm-childnode1`:

```bash
sudo mkdir -p /mnt/cnas-mysql
sudo chown -R 999:999 /mnt/cnas-mysql
sudo chmod 700 /mnt/cnas-mysql
```

If your worker hostname is different, update `k8s/local-storage.yaml`.

## Build and publish the application image

Kubernetes worker nodes must be able to pull the web image. The easiest method is Docker Hub or GHCR.

Example using Docker Hub:

```bash
docker build -t YOUR_DOCKERHUB_USERNAME/cnas-php-mysql:latest .
docker login
docker push YOUR_DOCKERHUB_USERNAME/cnas-php-mysql:latest
```

After deployment, set the Kubernetes image:

```bash
kubectl -n cnas set image deployment/cnas-web web=docker.io/YOUR_DOCKERHUB_USERNAME/cnas-php-mysql:latest
```

Alternative for lab-only testing: export the image and import it into containerd on every worker node:

```bash
docker save cnas-php-mysql:local -o cnas-php-mysql.tar
scp cnas-php-mysql.tar user@192.168.2.22:/tmp/
scp cnas-php-mysql.tar user@192.168.2.23:/tmp/
ssh user@192.168.2.22 "sudo ctr -n k8s.io images import /tmp/cnas-php-mysql.tar"
ssh user@192.168.2.23 "sudo ctr -n k8s.io images import /tmp/cnas-php-mysql.tar"
```

Using a registry is cleaner for the report and CI/CD pipeline.

## Deploy the assignment manifests

Run from the assignment folder on `vm-master`:

```bash
kubectl apply -k k8s
kubectl -n cnas get pods -o wide
kubectl -n cnas get svc,ingress,hpa,pdb,pvc
```

If the web pods show `ImagePullBackOff`, set the image to your pushed registry image:

```bash
kubectl -n cnas set image deployment/cnas-web web=docker.io/YOUR_DOCKERHUB_USERNAME/cnas-php-mysql:latest
kubectl -n cnas rollout status deployment/cnas-web --timeout=180s
```

## Ingress using your Week 12 NGINX Ingress Controller

Your Week 12 lab used NGINX Ingress Controller exposed as NodePort. Check it:

```bash
kubectl get svc -n ingress-nginx ingress-nginx-controller
kubectl get pods -n ingress-nginx
```

Find the HTTP NodePort, for example `31987`.

The assignment Ingress uses host:

```text
cnas.local
```

Test:

```bash
curl -H "Host: cnas.local" http://192.168.2.21:HTTP_NODEPORT/health.php
curl -H "Host: cnas.local" http://192.168.2.21:HTTP_NODEPORT/health.php?deep=1
```

Browser format:

```text
http://cnas.local:HTTP_NODEPORT
```

Add this line to your laptop or VM `/etc/hosts`:

```text
192.168.2.21 cnas.local
```

## Optional TLS using your Week 12 method

Create a self-signed certificate:

```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout tls.key -out tls.crt \
  -subj "/CN=cnas.local/O=cnas.local"
```

Create a TLS secret in the `cnas` namespace:

```bash
kubectl -n cnas create secret tls cnas-tls-secret --cert=tls.crt --key=tls.key
```

Then add this to `k8s/ingress.yaml` under `spec` if you want HTTPS evidence:

```yaml
tls:
  - hosts:
      - cnas.local
    secretName: cnas-tls-secret
```

## HPA requirement

Your Week 12 monitoring lab does not automatically give HPA CPU metrics. Check:

```bash
kubectl top nodes
kubectl top pods -n cnas
```

If this fails, install metrics-server:

```bash
kubectl apply -f https://github.com/kubernetes-sigs/metrics-server/releases/latest/download/components.yaml
kubectl -n kube-system rollout status deployment/metrics-server --timeout=180s
```

Then check:

```bash
kubectl -n cnas get hpa
```

## Monitoring options

You can reuse the Week 12 Prometheus and Grafana approach, or use the Helm-based monitoring plan in:

```text
docs/monitoring-plan.md
```

For the report, either option is acceptable if you capture real screenshots of:

- Prometheus or Grafana running
- Node CPU/memory metrics
- Pod metrics
- Pod restarts or availability
- Kubernetes events/logs

## Assignment evidence to capture from these VMs

- VMware showing 3 VMs running
- `kubectl get nodes -o wide`
- `kubectl -n cnas get pods -o wide`
- Web pods spread across `vm-childnode1` and `vm-childnode2`
- `kubectl -n cnas get svc,ingress,hpa,pdb,pvc`
- Ingress Controller NodePort
- App reachable at `cnas.local`
- Pod deletion self-healing
- HPA output
- MySQL PVC bound
- Grafana dashboard
- GitHub Actions pipeline run
