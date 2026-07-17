APP_IMAGE ?= cnas-php-mysql:local
NAMESPACE ?= cnas

.PHONY: docker-build docker-up docker-down k8s-apply k8s-delete k8s-status test

docker-build:
	docker build -t $(APP_IMAGE) .

docker-up:
	docker compose up --build -d

docker-down:
	docker compose down

k8s-apply:
	kubectl apply -k k8s

k8s-delete:
	kubectl delete -k k8s

k8s-status:
	kubectl get nodes -o wide
	kubectl -n $(NAMESPACE) get pods -o wide
	kubectl -n $(NAMESPACE) get svc,ingress,hpa,pdb,pvc

test:
	bash scripts/smoke-test.sh http://localhost:8080

