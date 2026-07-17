# CNAS 2026 PHP-MySQL Cloud Native Assignment

This project implements the CNAS Assignment 2026/27 Semester 5 sample PHP-MySQL CRUD application and packages it for Docker, Kubernetes, monitoring, and secure CI/CD demonstration.

The implementation is intentionally simple and explainable: a PHP Apache web tier, a MySQL database tier, Docker Compose for local testing, Kubernetes manifests for a 3-replica web deployment, NGINX Ingress as the API gateway entry point, HPA for scaling, PDB for availability, NetworkPolicy for traffic restriction, and CI/CD checks for syntax, container build, vulnerability scanning, and manifest validation.

## TODOs before submission

- Replace `TODO_T01_OR_T02`, `TODO_TEAM_NUMBER`, and `TODO_MEMBER_NAMES` in `index.php`.
- Replace sample users in `users.sql` and `k8s/mysql-initdb-configmap.yaml`.
- Replace Kubernetes secret placeholders locally before real deployment. Do not commit real passwords.
- Replace registry placeholders in `.github/workflows/ci-cd.yml` and documentation if not using GHCR.
- Capture real screenshots and command outputs listed in `docs/evidence-checklist.md`.
- Add actual problems/resolutions and team contribution details to the final report.

## Repository structure

```text
.
|-- db.php, index.php, create.php, update.php, delete.php, health.php
|-- users.sql
|-- Dockerfile
|-- docker-compose.yml
|-- k8s/
|-- monitoring/
|-- scripts/
|-- .github/workflows/ci-cd.yml
|-- SECURITY.md
`-- docs/
```

## Prerequisites

- Docker and Docker Compose
- curl
- kubectl
- Helm, for Prometheus and Grafana monitoring
- Optional: make, hey or ApacheBench for load testing

## Local Docker run

```bash
docker compose up --build -d
docker compose ps
curl http://localhost:8080/health.php
curl http://localhost:8080/health.php?deep=1
```

Open `http://localhost:8080` in a browser and test create, read, update, and delete.

Stop the stack:

```bash
docker compose down
```

Remove the local MySQL volume only when you intentionally want to reset data:

```bash
docker compose down -v
```

## Useful make targets

```bash
make docker-build
make docker-up
make test
make docker-down
make k8s-apply
make k8s-status
make k8s-delete
```

On Windows without `make`, run the equivalent Docker, kubectl, and PowerShell commands shown in this README.

## Chosen Kubernetes deployment target

This project will use the existing school practical VMware Ubuntu kubeadm VMs as the main Kubernetes environment:

- `vm-master` / `192.168.2.21` as the master/control-plane VM
- `vm-childnode1` / `192.168.2.22` as worker 1
- `vm-childnode2` / `192.168.2.23` as worker 2

Use `docs/vmware-ubuntu-setup.md` to reuse and verify the cluster. This gives strong assignment evidence because the worker nodes are actual virtual machines and you already completed the Week 11 and Week 12 practical labs on this setup.

Prepare MySQL local storage on `vm-childnode1`:

```bash
sudo mkdir -p /mnt/cnas-mysql
sudo chown -R 999:999 /mnt/cnas-mysql
sudo chmod 700 /mnt/cnas-mysql
```

Apply the manifests from `vm-master`:

```bash
kubectl apply -k k8s
kubectl -n cnas rollout status statefulset/cnas-mysql --timeout=180s
kubectl -n cnas rollout status deployment/cnas-web --timeout=180s
```

Verify:

```bash
kubectl -n cnas get pods -o wide
kubectl -n cnas get svc,ingress,hpa,pdb,pvc
kubectl -n cnas describe deployment cnas-web
```

Access through the Week 12 NGINX Ingress NodePort:

```bash
kubectl get svc -n ingress-nginx ingress-nginx-controller
curl -H "Host: cnas.local" http://192.168.2.21:HTTP_NODEPORT/health.php
curl -H "Host: cnas.local" http://192.168.2.21:HTTP_NODEPORT/health.php?deep=1
```

Add `192.168.2.21 cnas.local` to your local hosts file and browse to `http://cnas.local:HTTP_NODEPORT`.

## Demo commands

Show self-healing:

```bash
kubectl -n cnas get pods
kubectl -n cnas delete pod -l app.kubernetes.io/name=cnas-web
kubectl -n cnas get pods -w
```

Show manual scaling:

```bash
kubectl -n cnas scale deployment/cnas-web --replicas=5
kubectl -n cnas get pods -o wide
kubectl -n cnas scale deployment/cnas-web --replicas=3
```

Generate load for the HPA from the ingress namespace:

```bash
kubectl -n ingress-nginx run hey --rm -i --restart=Never --image=rakyll/hey -- \
  -z 2m -c 50 http://cnas-web.cnas.svc.cluster.local/
kubectl -n cnas get hpa -w
```

Check logs and events:

```bash
kubectl -n cnas logs deployment/cnas-web
kubectl -n cnas get events --sort-by=.lastTimestamp
```

## Monitoring

The monitoring plan uses kube-prometheus-stack for Prometheus, Grafana, alerting primitives, Kubernetes events, and resource dashboards.

```bash
helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
helm repo update
helm upgrade --install cnas-monitoring prometheus-community/kube-prometheus-stack \
  --namespace monitoring \
  --create-namespace \
  -f monitoring/kube-prometheus-stack-values.yaml
```

See `docs/monitoring-plan.md` for metrics to capture and screenshot evidence.

## CI/CD

GitHub Actions is the preferred pipeline:

- PHP syntax check
- Docker Compose config validation
- Kubernetes manifest rendering and kubeconform validation
- Docker image build
- Trivy vulnerability scan
- Push to GHCR on `main`
- Gated Kubernetes deployment using `KUBE_CONFIG_DATA`

See `docs/cicd-plan.md` for the pipeline explanation and diagram.

## Security features

- DB credentials are read from environment variables.
- PHP SQL operations use prepared statements.
- ID query parameters are validated as positive integers.
- Displayed output is HTML escaped.
- Web container runs as non-root on port 8080.
- Kubernetes web deployment uses 3 replicas, resource requests/limits, probes, PDB, HPA, and restricted security context.
- NetworkPolicy restricts web ingress to the ingress/API gateway namespace and permits only web-to-MySQL database traffic.
- CI/CD includes image vulnerability scanning and manifest validation.

See `SECURITY.md` for the requirement-mapped security checklist.

## Troubleshooting

- If `health.php?deep=1` fails, check `kubectl -n cnas logs statefulset/cnas-mysql` and confirm the secret values match the app DB credentials.
- If web pods stay `ImagePullBackOff`, push the Docker image to Docker Hub/GHCR and update the image in the Deployment.
- If HPA shows `<unknown>`, install metrics-server and wait a few minutes for metrics.
- If ingress does not respond, confirm the NGINX controller is ready in `ingress-nginx` and use the `Host: cnas.local` header.
- If the MySQL PVC stays pending, confirm `/mnt/cnas-mysql` exists on `vm-childnode1` and that `k8s/local-storage.yaml` uses the correct worker hostname.
