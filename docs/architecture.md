# Architecture

These diagrams can be used in the report after the team updates names, registry, hostnames, and screenshots.

## High-level application architecture

```mermaid
flowchart LR
    User["User / Tutor Browser"] --> LB["Cloud Load Balancer or kind host port"]
    LB --> Gateway["NGINX Ingress Controller / API Gateway"]
    Gateway --> WebSvc["cnas-web Service"]
    WebSvc --> WebPods["3 x PHP Apache Web Pods"]
    WebPods --> MySQLSvc["cnas-mysql ClusterIP Service"]
    MySQLSvc --> MySQL["MySQL StatefulSet"]
    MySQL --> PVC["PersistentVolumeClaim"]
```

## Kubernetes deployment view

```mermaid
flowchart TB
    subgraph IngressNS["ingress-nginx namespace"]
        Nginx["NGINX Ingress Controller"]
    end

    subgraph CNAS["cnas namespace"]
        Ingress["Ingress cnas.local"]
        Service["Service cnas-web"]
        Deploy["Deployment cnas-web replicas=3"]
        HPA["HorizontalPodAutoscaler min=3 max=6"]
        PDB["PodDisruptionBudget minAvailable=2"]
        Config["ConfigMap DB host/name/port"]
        Secret["Secret DB credentials placeholder"]
        DBService["Service cnas-mysql"]
        DBStateful["StatefulSet cnas-mysql"]
        DBPVC["PVC mysql-data"]
        NetPol["NetworkPolicies default-deny plus allowed flows"]
    end

    Nginx --> Ingress --> Service --> Deploy
    HPA --> Deploy
    PDB --> Deploy
    Deploy --> Config
    Deploy --> Secret
    Deploy --> DBService --> DBStateful --> DBPVC
    DBStateful --> Secret
    NetPol -.restricts.-> Deploy
    NetPol -.restricts.-> DBStateful
```

## CI/CD flow

```mermaid
flowchart LR
    Dev["Developer Commit"] --> GitHub["GitHub Repository"]
    GitHub --> Validate["PHP lint + Compose check + K8s validation"]
    Validate --> Build["Docker Build"]
    Build --> Scan["Trivy Image Scan"]
    Scan --> Push["Push Image to Registry on main"]
    Push --> Gate["Protected Deployment Environment"]
    Gate --> Deploy["kubectl apply to Kubernetes"]
    Deploy --> Rollout["Rollout Status + Audit Trail"]
```

## Monitoring flow

```mermaid
flowchart LR
    Kubelet["Kubelet / cAdvisor"] --> Prom["Prometheus"]
    KSM["kube-state-metrics"] --> Prom
    Node["Node Exporter"] --> Prom
    Events["Kubernetes Events"] --> Review["kubectl describe / get events"]
    Prom --> Grafana["Grafana Dashboards"]
    Web["cnas-web Pods"] --> Logs["kubectl logs / central logging recommendation"]
    MySQL["cnas-mysql Pod + PVC"] --> Prom
```

## Design notes

- The web tier is stateless and horizontally scalable.
- The MySQL tier uses persistent storage so pod restart does not remove data.
- NGINX Ingress is the API gateway and external routing component.
- In the school VMware plan, NGINX Ingress is exposed through NodePort, following the Week 12 practical.
- Internal load balancing is provided by Kubernetes Services across the web pods. If a true `LoadBalancer` Service is required, MetalLB can be added as an optional enhancement.
- For production database high availability, use managed MySQL or a MySQL operator with replication. This assignment implementation keeps the database simple and demonstrable.
