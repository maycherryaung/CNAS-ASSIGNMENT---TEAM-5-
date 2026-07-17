# Monitoring and Auditing Plan

## Monitoring stack

Use kube-prometheus-stack, which installs Prometheus, Grafana, Alertmanager, kube-state-metrics, and node exporter.

```bash
helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
helm repo update
helm upgrade --install cnas-monitoring prometheus-community/kube-prometheus-stack \
  --namespace monitoring \
  --create-namespace \
  -f monitoring/kube-prometheus-stack-values.yaml
```

Access Grafana locally:

```bash
kubectl -n monitoring port-forward svc/cnas-monitoring-grafana 3000:80
```

Open `http://localhost:3000`. Use the configured demo password only for local testing and change it before any real deployment.

## Critical metrics

| Area | Metrics to capture | Why it matters |
| --- | --- | --- |
| Web pods | CPU, memory, restarts, ready replicas | Shows availability, resource use, and self-healing |
| HPA | current replicas, desired replicas, CPU utilization | Shows scalability |
| Deployment | available replicas, rollout status | Shows high availability during changes |
| Nodes | CPU, memory, disk, pod count | Shows worker-node capacity |
| MySQL | pod readiness, restarts, PVC usage | Shows database availability and storage health |
| Ingress | request rate, errors, latency if configured | Shows API gateway and load-balancer behavior |
| Events | scheduling failures, probe failures, image pull errors | Useful for troubleshooting and auditing |

## Audit and logging plan

Implemented:

- `kubectl logs deployment/cnas-web -n cnas`
- `kubectl logs statefulset/cnas-mysql -n cnas`
- `kubectl get events -n cnas --sort-by=.lastTimestamp`
- GitHub Actions run history for build, scan, validation, and deployment evidence
- Trivy scan output in CI/CD logs

Recommended for production:

- Enable Kubernetes API audit logs in the managed cluster or kubeadm control plane.
- Export container logs to a central log store.
- Keep deployment approvals in a protected GitHub environment.
- Retain vulnerability reports and manifest validation logs as release evidence.

## Screenshots to capture

- Grafana node CPU/memory dashboard
- Grafana Kubernetes pods dashboard filtered to namespace `cnas`
- HPA status before and during load test
- Pod restart count and readiness
- MySQL PVC status
- Kubernetes events after self-healing demo
- CI/CD Trivy scan output

