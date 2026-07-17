# Evidence Checklist

Do not fabricate evidence. Capture screenshots and command outputs only after the commands actually run.

## Local Docker

- [ ] Docker build success
- [ ] `docker compose ps`
- [ ] App home page
- [ ] Create member form
- [ ] Update member form
- [ ] Delete confirmation
- [ ] `curl http://localhost:8080/health.php`
- [ ] `curl http://localhost:8080/health.php?deep=1`

## Kubernetes cluster

- [ ] VMware VM list showing 1 control-plane/master VM and 2-3 worker VMs
- [ ] `kubectl get nodes -o wide` showing 2-3 worker nodes, preferably 3
- [ ] Ubuntu VM IP addresses and hostnames documented
- [ ] NGINX Ingress Controller NodePort from the Week 12 setup
- [ ] NGINX Ingress Controller pods ready
- [ ] metrics-server running
- [ ] `kubectl -n cnas get pods -o wide`
- [ ] Web replicas spread across worker nodes where possible
- [ ] `kubectl -n cnas get svc`
- [ ] `kubectl -n cnas get ingress`
- [ ] `kubectl -n cnas get hpa`
- [ ] `kubectl -n cnas get pdb`
- [ ] `kubectl -n cnas get pvc`
- [ ] Ingress health curl

## HA, resiliency, scalability

- [ ] Before/after pod deletion self-healing
- [ ] Manual scale to 5 replicas and back to 3
- [ ] HPA before load
- [ ] Load test command running
- [ ] HPA after load showing CPU/replica change, if load is enough
- [ ] MySQL pod deletion with data retained

## Security

- [ ] Code snippets showing prepared statements
- [ ] Kubernetes Secret template with placeholders only
- [ ] Web pod security context
- [ ] NetworkPolicy output
- [ ] Trivy scan output
- [ ] CI/CD protected deployment settings, if used

## Monitoring and auditing

- [ ] Grafana Kubernetes nodes dashboard
- [ ] Grafana namespace/pods dashboard for `cnas`
- [ ] Pod restart metrics
- [ ] HPA metrics
- [ ] `kubectl logs deployment/cnas-web`
- [ ] `kubectl get events -n cnas --sort-by=.lastTimestamp`

## CI/CD

- [ ] GitHub Actions successful validation job
- [ ] Docker build in pipeline
- [ ] Trivy scan in pipeline
- [ ] Kubernetes validation in pipeline
- [ ] Image pushed to registry, if enabled
- [ ] Deployment rollout, if enabled
