# 20-25 Minute Demo Script

## Suggested timing

| Time | Presenter | Segment |
| --- | --- | --- |
| 0:00-2:00 | Member 1 | Problem statement and architecture overview |
| 2:00-6:00 | Member 1 | Docker and local app demo |
| 6:00-11:00 | Member 2 | Kubernetes deployment, nodes, pods, services, ingress |
| 11:00-15:00 | Member 2 | HA, self-healing, scaling, HPA, PDB |
| 15:00-18:00 | Member 3 | Security controls and network policies |
| 18:00-21:00 | Member 3 | Monitoring and auditing |
| 21:00-24:00 | Member 4 | CI/CD pipeline and evidence |
| 24:00-25:00 | All | Questions |

Adjust the member names and timings based on team size.

## Commands to prepare

Local app:

```bash
docker compose up --build -d
curl http://localhost:8080/health.php
curl http://localhost:8080/health.php?deep=1
```

Kubernetes overview:

```bash
kubectl get nodes -o wide
kubectl -n cnas get pods -o wide
kubectl -n cnas get svc,ingress,hpa,pdb,pvc
kubectl -n cnas describe deployment cnas-web
```

Ingress:

```bash
curl -H "Host: cnas.local" http://localhost/health.php
curl -H "Host: cnas.local" http://localhost/health.php?deep=1
```

Self-healing:

```bash
kubectl -n cnas delete pod -l app.kubernetes.io/name=cnas-web
kubectl -n cnas get pods -w
```

Manual scaling:

```bash
kubectl -n cnas scale deployment/cnas-web --replicas=5
kubectl -n cnas get pods -o wide
kubectl -n cnas scale deployment/cnas-web --replicas=3
```

HPA load test:

```bash
kubectl -n ingress-nginx run hey --rm -i --restart=Never --image=rakyll/hey -- \
  -z 2m -c 50 http://cnas-web.cnas.svc.cluster.local/
kubectl -n cnas get hpa -w
```

Monitoring:

```bash
kubectl -n monitoring get pods
kubectl -n monitoring port-forward svc/cnas-monitoring-grafana 3000:80
```

Logs and events:

```bash
kubectl -n cnas logs deployment/cnas-web
kubectl -n cnas get events --sort-by=.lastTimestamp
```

## Likely tutor questions and model answers

| Question | Model answer |
| --- | --- |
| How is the app highly available? | The web tier runs 3 replicas behind a Service. If one pod fails, the Deployment recreates it and the Service routes traffic only to ready pods. |
| How is load balancing implemented? | Kubernetes Service load balances internally across web pods. NGINX Ingress provides external routing/API gateway. In cloud, the ingress controller is normally exposed by a LoadBalancer Service. |
| How do you prove resiliency? | Delete a web pod and show Kubernetes recreates it. Readiness and liveness probes keep unhealthy pods out of service and restart failed containers. |
| How does scaling work? | HPA watches CPU metrics from metrics-server and scales the web Deployment from 3 to 6 replicas when CPU utilization rises. |
| Where are secrets stored? | The application reads secrets from environment variables. Kubernetes uses a Secret template with placeholders. Real deployments should use a secret manager or create the Secret securely outside Git. |
| What prevents SQL injection? | CRUD operations use prepared statements, and IDs are validated as positive integers. |
| What prevents XSS? | Displayed values are escaped before being printed into HTML. |
| What does NetworkPolicy do? | It denies default pod traffic, allows ingress only from the ingress namespace to the web tier, and allows the web tier to connect to MySQL. |
| Is MySQL highly available? | In this assignment implementation, MySQL is persistent but single-replica for simplicity. For production, use managed MySQL or a replicated MySQL operator. |
| How is CI/CD secure? | The pipeline checks PHP syntax, validates manifests, builds the image, scans with Trivy, pushes versioned images, and gates deployment through a protected environment. |

