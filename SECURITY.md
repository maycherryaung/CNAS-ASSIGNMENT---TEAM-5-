# Security Notes and Checklist

This project separates what is implemented, what is prepared for the assignment demo, and what is recommended for a production environment.

## Implemented

| Area | Control | Evidence |
| --- | --- | --- |
| Input handling | Positive integer validation for `id` values in `update.php` and `delete.php` | Code review and invalid-id browser/curl test |
| SQL injection reduction | Prepared statements for SELECT, INSERT, UPDATE, and DELETE | PHP files |
| XSS reduction | HTML output is escaped with `htmlspecialchars` helper | `index.php`, `create.php`, `update.php`, `delete.php` |
| Secret handling | App reads `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, and `DB_PORT` from environment variables | `db.php`, Docker Compose, Kubernetes ConfigMap/Secret |
| Container hardening | Web container runs as `www-data` on port 8080 instead of binding port 80 as root | `Dockerfile`, `k8s/web-deployment.yaml` |
| Kubernetes least privilege | Web pod uses a dedicated ServiceAccount with token automount disabled and no RBAC permissions | `k8s/web-serviceaccount.yaml` |
| Runtime restrictions | `allowPrivilegeEscalation: false`, dropped Linux capabilities, resource limits, and `RuntimeDefault` seccomp | Kubernetes manifests |
| Network restriction | Default-deny policy plus explicit web ingress and web-to-MySQL rules | `k8s/networkpolicy.yaml` |
| Availability controls | 3 web replicas, PDB, HPA, readiness/liveness probes | `k8s/web-deployment.yaml`, `k8s/hpa.yaml`, `k8s/pdb.yaml` |
| CI/CD security gates | PHP syntax check, Docker build, Trivy image scan, kubeconform validation | `.github/workflows/ci-cd.yml` |

## Prepared for demo

| Area | Prepared item | What the team must do |
| --- | --- | --- |
| Kubernetes secrets | `k8s/secret-template.yaml` has placeholder values | Replace locally or create the Secret from a secret manager before real deployment |
| TLS | Ingress is ready for TLS annotations and a `tls` block | Add a real DNS name, certificate, and set SSL redirect to true |
| Monitoring | kube-prometheus-stack values and setup commands are documented | Install the Helm chart and capture actual dashboards |
| Image registry | GitHub Actions can push to GHCR | Confirm repository permissions and package visibility |
| Deployment automation | GitHub Actions deploy job expects `KUBE_CONFIG_DATA` | Add a protected environment and base64 kubeconfig secret |

## Recommended for production

- Use a managed database or a replicated MySQL operator instead of single-pod MySQL.
- Store secrets in a cloud secret manager, External Secrets Operator, Sealed Secrets, or SOPS.
- Pin container images by digest after testing.
- Enable HTTPS with a trusted certificate and force redirect to TLS.
- Add a Web Application Firewall or gateway-level request filtering if exposed publicly.
- Add CSRF tokens for form submissions if this app becomes internet-facing.
- Send application logs to a central logging system.
- Enable Kubernetes audit logging on a managed or kubeadm cluster.
- Run regular dependency and image scans, and review Trivy findings before deployment.

## Secret handling rule

Never commit real database passwords, kubeconfigs, registry tokens, cloud keys, or Grafana admin passwords. Evidence screenshots should hide tokens and private URLs where needed.

