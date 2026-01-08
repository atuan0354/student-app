# Student-Management-System
Quản lý sinh viên PHP
Hệ thống quản lý sinh viên bao gồm các tính năng:
- Đăng nhập (lưu phiên Session)
- Quản lý danh sách sinh viên (thêm sửa xóa)
- Quản lý danh sách môn học (thêm sửa xóa)
- Quản lý danh sách lớp học (thêm sửa xóa)
- Quản lý danh sách sinh viên trong lớp học (thêm sửa xóa)
- Quản lý điểm sinh viên (tổng kết điểm, tự động tính điểm trung bình hệ chữ và hệ số, thêm sửa xóa điểm)
# Mô hình hệ thống: M-V-C
HTML view <-> jQuery -> PHP Controller <-> PHP Model <-> Mysql database
Database thiết kế chuẩn 3 (3rd Normal Form), truy vấn + thay đổi dữ liệu qua Stored procedure
# REQUIREMENTS
- Apache PHP 5 (XAMPP)
- MySQL 5
- jQuery 3
- Bootstrap 4

# HOW TO USE.

- Clone project vào thư mục htdocs.
- Tạo 1 database mới với tên là "qlsv", charset là UTF8, collation là utf8_unicode_ci
- Import file qlsv.sql vào db vừa tạo.



<img width="1025" height="490" alt="image" src="https://github.com/user-attachments/assets/0c8b1985-e650-4408-99b4-97823a417790" 

Triển khai hệ thống Student Management System (PHP + MySQL) theo mô hình Kubernetes + HAProxy + Domain + CI/CD GitOps (Jenkins + GHCR + Helm + ArgoCD) + Logging/Monitoring
1) Mục tiêu Lab

Triển khai hệ thống Student Management System (PHP + MySQL) theo mô hình:

Ứng dụng PHP chạy trên Kubernetes

Database MySQL chạy riêng trên máy DB

Người dùng truy cập Web qua Domain vtisa03.com.vn → HAProxy → Ingress NGINX → Service → Pod

Docker image lưu trên GHCR (GitHub Container Registry)

Jenkins thực hiện CI: build & push image, update Helm chart

Helm quản lý toàn bộ manifest Kubernetes

ArgoCD GitOps tự đồng bộ và deploy khi Helm chart thay đổi

Tích hợp Logging ELK (Elasticsearch + Logstash + Kibana) và Monitoring Prometheus + Grafana (tuỳ chọn)

2) Topology Lab (Hạ tầng)
2.1 pfSense Firewall (Router/Firewall/NAT)

pfSense đóng vai trò firewall, chia network thành các vùng:

Interface	IP	Chức năng
WAN	DHCP	Lấy IP Internet tự động
LAN	172.16.1.11	Quản trị / backup / logging / monitoring
DMZ	192.168.2.11	Vùng dịch vụ (HAProxy + Kubernetes)
DB	192.168.3.11	Vùng cơ sở dữ liệu
2.2 Danh sách máy và vai trò
Role	OS	IP
PC LAN (Backup + Logging + Monitoring)	Ubuntu Desktop	172.16.1.132
HAProxy + Domain vtisa03.com.vn	Ubuntu Desktop	192.168.2.132
Kubernetes Master	Ubuntu Desktop	192.168.2.133
Kubernetes Worker1	Ubuntu Server	192.168.2.134
Kubernetes Worker2	Ubuntu Server	192.168.2.135
Database MySQL	Ubuntu Desktop	192.168.3.132
2.3 Domain và DNS

Domain đã cấu hình trỏ về HAProxy:

✅ vtisa03.com.vn → 192.168.2.132

3) Kiến trúc hệ thống triển khai
3.1 Luồng truy cập Web

Client → vtisa03.com.vn → HAProxy (192.168.2.132)
→ Forward vào NodePort Ingress NGINX (K8s)
→ Ingress Rules → Service (student-svc) → Pod (student-app)

3.2 Luồng truy cập Database

Pod student-app (Kubernetes)
→ pfSense DMZ/DB routing
→ MySQL server (192.168.3.132)

4) Triển khai Student Management System (PHP + MySQL)
4.1 Docker Image và GHCR

Image được lưu trên GHCR:

✅ ghcr.io/atuan0354/student-management:<tag>

Ví dụ build & push:

docker build -t ghcr.io/atuan0354/student-management:latest .
docker push ghcr.io/atuan0354/student-management:latest

4.2 Namespace và Secret DB

Tạo namespace:

kubectl create ns student


Tạo secret DB chứa thông tin kết nối:

DB_HOST: 192.168.3.132

DB_USER: studentuser

DB_PASS: StrongPass@123

DB_NAME: studentdb

kubectl -n student create secret generic db-secret \
  --from-literal=DB_HOST=192.168.3.132 \
  --from-literal=DB_USER=studentuser \
  --from-literal=DB_PASS='StrongPass@123' \
  --from-literal=DB_NAME=studentdb

4.3 Deploy App lên Kubernetes

Trong namespace student triển khai:

Deployment: student-app

Service: student-svc

Ingress: student-ingress

Host: vtisa03.com.vn

4.4 Pull Image từ GHCR bằng imagePullSecret

Tạo secret để pull image:

kubectl -n student create secret docker-registry ghcr-secret \
  --docker-server=ghcr.io \
  --docker-username=atuan0354 \
  --docker-password=<YOUR_GHCR_PAT> \
  --docker-email=atuan0354@gmail.com


Patch vào serviceaccount:

kubectl -n student patch serviceaccount default \
  -p '{"imagePullSecrets":[{"name":"ghcr-secret"}]}'

5) Database Server (MySQL)
5.1 Thông tin máy DB

IP: 192.168.3.132

DB: studentdb

User: studentuser

5.2 Import dữ liệu
mysql -u studentuser -p studentdb < qlsv.sql

6) HAProxy + Ingress NGINX + Domain
6.1 HAProxy (192.168.2.132)

Tác dụng:

Nhận request từ internet/domain vtisa03.com.vn

Forward traffic vào NodePort của Ingress Controller Kubernetes

6.2 Ingress NGINX (trong Kubernetes)

Namespace:

ingress-nginx

Ingress Controller publish NodePort để HAProxy forward.

6.3 Test tạo log HAProxy
curl -I http://vtisa03.com.vn/
curl -I http://vtisa03.com.vn/employee.php
curl -I http://vtisa03.com.vn/member.php

7) Helm (quản lý manifest)

Repo Helm chart:

✅ https://github.com/atuan0354/student-helm

Cấu trúc:

student-app/
  Chart.yaml
  values.yaml
  templates/
    deployment.yaml
    service.yaml
    ingress.yaml


Helm dùng để:

quản lý cấu hình deploy theo version

update nhanh image tag / ingress host / resources

dễ dàng dùng GitOps với ArgoCD

8) ArgoCD GitOps Auto Deploy
8.1 Expose ArgoCD UI
kubectl -n argocd patch svc argocd-server -p '{"spec":{"type":"NodePort"}}'
kubectl -n argocd get svc argocd-server


Lấy password admin:

kubectl -n argocd get secret argocd-initial-admin-secret \
  -o jsonpath="{.data.password}" | base64 -d ; echo

8.2 Application GitOps

ArgoCD theo dõi repo student-helm:

Khi Helm chart thay đổi (values.yaml đổi tag)

ArgoCD tự sync và rollout version mới

9) Jenkins CI/CD Pipeline
9.1 Jenkins UI

Jenkins chạy container:

✅ http://192.168.2.133:8081

9.2 Jenkins Credentials

ghcr-creds: login GHCR để push image

github-creds: push update lên repo Helm

9.3 Luồng Pipeline

Pipeline job student-cicd:

Checkout repo student-app

Build image ghcr.io/...:<BUILD_NUMBER>

Push image lên GHCR

Clone repo Helm

Update values.yaml tag mới

Commit + push repo Helm

ArgoCD auto deploy

10) Logging ELK (Optional)
10.1 Kiến trúc Logging

Filebeat → Logstash → Elasticsearch → Kibana

ELK chạy trên PC LAN: 172.16.1.132

Ports:

Elasticsearch: 172.16.1.132:9200

Kibana: 172.16.1.132:5601

Logstash Beats input: 172.16.1.132:5044

10.2 Tách index theo service

Student app logs: student-logs-*

HAProxy logs: haproxy-logs-*

10.3 Kibana Data Views

✅ Data View 1: HAProxy

Name: haproxy-logs-*

Index pattern: haproxy-logs-*

Time field: @timestamp

✅ Data View 2: Student

Name: student-logs-*

Index pattern: student-logs-*

Time field: @timestamp

11) Monitoring Prometheus + Grafana (Optional)

Chạy trên PC LAN: 172.16.1.132

Tác dụng:

Prometheus thu thập metrics node/k8s

Grafana hiển thị dashboard CPU/RAM/Pod/Network

12) Tổng hợp nhanh URL theo đúng Lab và tác dụng
Service	URL	Tác dụng
Website Student App	http://vtisa03.com.vn/	Truy cập web qua HAProxy + Ingress
Employee Page	http://vtisa03.com.vn/employee.php	Test chức năng + tạo log HAProxy
Member Page	http://vtisa03.com.vn/member.php	Test chức năng + tạo log HAProxy
Elasticsearch	http://172.16.1.132:9200	Kiểm tra cluster / index / search
Kibana	http://172.16.1.132:5601	Xem log, tạo dashboard
Logstash	172.16.1.132:5044	Cổng Beats input (Filebeat gửi log vào)
ArgoCD	http://192.168.2.133:<NodePort>	Quản lý GitOps deploy
Jenkins	http://192.168.2.133:8081	CI/CD build-push-update chart
Grafana	http://172.16.1.132:3000	Dashboard Monitoring
Prometheus	http://172.16.1.132:9090	Metrics server


