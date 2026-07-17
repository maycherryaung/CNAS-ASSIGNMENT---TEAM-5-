# Requirement Mapping

| Requirement | Implementation artifact | How to verify | Screenshot/evidence needed | Report section |
| --- | --- | --- | --- | --- |
| PHP-MySQL CRUD app | `index.php`, `create.php`, `update.php`, `delete.php`, `db.php`, `users.sql` | Browse app and perform CRUD | App pages and database-backed CRUD results | Application implementation |
| Use environment variables for DB config | `db.php`, `docker-compose.yml`, `k8s/configmap.yaml`, `k8s/secret-template.yaml` | Inspect env vars in Compose/Kubernetes | ConfigMap/Secret screenshots with secrets hidden | Security implementation |
| Prepared statements and safer IDs | PHP CRUD files | Review code and test invalid `id` values | Code snippets and invalid-id test | Security implementation |
| Health endpoint | `health.php` | `curl /health.php` and `/health.php?deep=1` | curl output | Testing and validation |
| Docker image | `Dockerfile`, `.dockerignore` | `docker build -t cnas-php-mysql:local .` | Build success | Docker implementation |
| Local 2-tier run | `docker-compose.yml` | `docker compose up --build -d` | `docker compose ps` and browser | Docker implementation |
| Kubernetes namespace | `k8s/namespace.yaml` | `kubectl get ns cnas` | namespace output | Kubernetes implementation |
| ConfigMap | `k8s/configmap.yaml` | `kubectl -n cnas get configmap cnas-app-config -o yaml` | ConfigMap output | Kubernetes implementation |
| Secret template | `k8s/secret-template.yaml` | `kubectl -n cnas get secret cnas-db-secret` | Secret exists, values hidden | Security implementation |
| MySQL persistence | `k8s/mysql-statefulset.yaml`, `k8s/mysql-service.yaml` | Delete MySQL pod and confirm data remains | PVC and persistence test | Resiliency |
| 3 web replicas | `k8s/web-deployment.yaml` | `kubectl -n cnas get deploy cnas-web` | Deployment replica output | HA/scalability |
| Internal load balancing | `k8s/web-service.yaml` | `kubectl -n cnas describe svc cnas-web` | Service endpoints | HA/load balancing |
| API gateway/ingress | `k8s/ingress.yaml` | curl with `Host: cnas.local` | Ingress and curl output | Architecture/Kubernetes |
| External load balancer path | Ingress controller setup in `README.md` and `docs/cluster-setup.md` | In managed cluster, check ingress controller LoadBalancer; in kind, use host port | Service or kind access evidence | Load balancing |
| HPA scaling | `k8s/hpa.yaml` | Run load test and `kubectl -n cnas get hpa -w` | HPA before/after | Scalability |
| PodDisruptionBudget | `k8s/pdb.yaml` | `kubectl -n cnas get pdb cnas-web` | PDB output | High availability |
| Resource requests/limits | Web and MySQL manifests | `kubectl -n cnas describe pod` | Resource section | Kubernetes implementation |
| Liveness/readiness probes | Web and MySQL manifests | `kubectl -n cnas describe pod` | Probe section and ready pods | Resiliency |
| Security contexts | Web and MySQL manifests | `kubectl -n cnas get pod -o yaml` | SecurityContext snippets | Security implementation |
| NetworkPolicy | `k8s/networkpolicy.yaml` | `kubectl -n cnas get networkpolicy` | NetworkPolicy output | Security implementation |
| Avoid unnecessary RBAC | `k8s/web-serviceaccount.yaml` | `kubectl -n cnas get role,rolebinding` | No unnecessary RBAC | Security implementation |
| 2-3 worker-node cluster | `docs/vmware-ubuntu-setup.md`, `kind-config.yaml`, `docs/cluster-setup.md` | `kubectl get nodes -o wide` | Node list showing Ubuntu VM workers or kind workers | Cluster setup |
| Monitoring | `monitoring/kube-prometheus-stack-values.yaml`, `docs/monitoring-plan.md` | Install Helm chart and open Grafana | Dashboards | Monitoring and auditing |
| Auditing/logging | `docs/monitoring-plan.md` | `kubectl logs`, `kubectl get events`, CI/CD run logs | Logs/events/screenshots | Monitoring and auditing |
| Secure CI/CD | `.github/workflows/ci-cd.yml`, `docs/cicd-plan.md` | Run GitHub Actions | Pipeline run and scan output | CI/CD pipeline |
| Report support | `docs/assignment-report-draft.md` | Fill TODO placeholders and insert screenshots | Final report sections | Assignment report |
| Demo support | `docs/demo-script.md` | Rehearse commands | Presentation checklist | Demonstration |
| Evidence checklist | `docs/evidence-checklist.md` | Tick completed evidence | Checklist in appendix | Testing and validation |
| Gen-AI usage reflection | `docs/report-outline.md` | Add truthful reflection and screenshots | Reflection section | Report appendix |
