# Épreuve finale à caractère synthèse

Date de remise le 10 octobre 2024

### Déploiement d'une structure web avec en tête un équilibreur de charge proxy dans un conteneur nginx
### qui dirige vers 2 serveurs web apache contnant php et MySql. Le tout séparé dans 3 conteneurs distincts.

Le site n'est pas fonctionnel, mais tout le travail pertinant est présent et comprit.



référence visuelles pour la video:

```yaml
[defaults]
inventory = ./inventaire.yaml
remote_user = admin
retry_files_enabled = False
log_path = ./.traces_d_ansible
```

```yaml
  GNU nano 7.2                                                             inventaire.yaml                                                                       
all:
  vars:
    ansible_ssh_common_args: '-o StrictHostKeyChecking=no'
lbservers:
  hosts:
    srv-lb1:
      ansible_host: 10.100.2.47
webservers:
  hosts:
    srv-web1:
      ansible_host: 10.100.2.180
    srv-web2:
      ansible_host: 10.100.2.48
prod:
  children:
    lbservers:
    webservers:
#  vars:
#    env: production # Pas sur de celle là et de son utilité
```

```yaml
- name: Configure LoadBalancer
  ansible.builtin.import_playbook: loadbalancer.yaml
- name: Configure Web Servers
  ansible.builtin.import_playbook: web.yaml
```

```yaml
- name: "Installation loadbalancer Nginx "
  hosts: lbservers
  become: true
  vars_files:
    - ./vars/secret-variables.yaml
  pre_tasks:
    - name: Install Docker
      apt:
        name: docker.io
        state: present
        update_cache: true

  tasks:

    - name: Create Directory
      ansible.builtin.file:
        path: /home/admin/NginxConf
        state: directory
        owner: admin
        group: admin
        mode: 0755

    - name: Upload default.conf
      copy:
        src: ./NginxConf/default.conf
        dest: /home/admin/NginxConf/default.conf
        mode: 0755

    - name: Create Nginx Container
      community.docker.docker_container:
        name: srv-lb1-proxy
        image: nginx:alpine
        ports:
          - "80:80"
        volumes:
          - /home/admin/NginxConf/default.conf:/usr/share/nginx/conf.d/default.conf:ro
        state: present
```

```yaml
- name: "Installation servers srv-web1, web2"
  hosts: webservers
  become: true
  vars_files:
    - ./vars/secret-variables.yaml
  pre_tasks:
    - name: Install Docker
      apt:
        name: docker.io
        state: present
        update_cache: true

  tasks:

    - name: Create Frontend Network
      community.docker.docker_network:
        name: frontend
        state: present

    - name: Create Backend Network
      community.docker.docker_network:
        name: backend
        state: present

    - name: Create Persistent MySQL Volume
      community.docker.docker_volume:
        name: mysql_data

    - name: Create Directory
      ansible.builtin.file:
        path: /home/admin/html
        state: directory
        owner: admin
        group: admin
        mode: 0755

    - name: Create Directory
      ansible.builtin.file:
        path: /home/admin/HttpdConf
        state: directory
        owner: admin
        group: admin
        mode: 0755

    - name: Uploade index.php
      copy:
        src: ./html/index.php
        dest: /home/admin/html/index.php
        mode: 0755

    - name: Upload httpd.conf
      copy:
        src: ./HttpdConf/httpd.conf
        dest: /home/admin/HttpdConf/httpd.conf
    
    - name: Create PHP-FPM Container
      community.docker.docker_container:
        name: php-fpm-container
        image: php:fpm-alpine
        ports:
          - "9000:9000"
        networks:
          - backend
        status: present

    - name: Create Apache Container
      community.docker.docker_container:
        name: apache2-container
        image: httpd:alpine
        ports:
          - "80:80"
        volumes:
          - /home/admin/html/index.php:/usr/local/apache2/htdocs/index.php
          - /home/admin/HttpdConf/httpd.conf:/usr/local/apache2/conf/httpd.conf
        networks:
          - frontend
          - backend
        state: present

    - name: Create MySQL Container
      community.docker.docker_container:
        name: mysql-container
        image: mysql:latest
        ports:
          - "3306:3306"
        volumes:
          - mysql_data:/var/lib/mysql
        environment:
          MYSQL_ROOT_PASSWORD: rootpassword
        networks:
          - backend
        state: present
```

















































