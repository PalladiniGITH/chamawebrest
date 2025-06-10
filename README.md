# chamawebrest

Este projeto demonstra uma arquitetura simples de microserviços em PHP. Os serviços comunicam-se através de APIs REST e um API Gateway centraliza o acesso externo.

## Serviços

- **gateway**: expõe as rotas públicas e encaminha as requisições para os microserviços internos.
- **tickets**: responsável pelo gerenciamento de chamados.
- **stats**: fornece estatísticas agregadas usadas na página de relatórios.
- **db**: banco de dados MySQL compartilhado entre os serviços.
- **nginx**: proxy reverso com HTTPS que expõe o portal e o gateway.
- **shared/connect.php**: script único de conexão ao banco utilizado pelos serviços.
- **public/**: arquivos PHP e recursos estáticos do portal web.

## Executando

Utilize o `docker-compose` para subir todos os serviços. O script `script_sql.sql` 
será executado automaticamente no primeiro start do banco, populando a tabela de
exemplo com um usuário administrador.

Credenciais padrão: `admin@sistema.com` / `admin123` (armazenada como hash SHA-256).

```bash
docker-compose up --build
```

O Apache do serviço **web** utiliza a pasta `public/` como DocumentRoot. Todas as páginas PHP e arquivos estáticos ficam nesse diretório.

O portal web pode ser acessado em `http://localhost:8080`.
O API Gateway estará em `http://localhost:8081` e fará a mediação das chamadas para os demais serviços.
Para acesso seguro via HTTPS, um contêiner Nginx é iniciado
automaticamente. Ele expõe o portal e o gateway em `https://localhost:8443`
(certificado autoassinado). Se você acessar `http://localhost:8443` verá um
erro "400 Bad Request" porque essa porta aceita apenas HTTPS.

## Endpoints

 Ao acessar o endereço acima, você verá uma mensagem com os caminhos disponíveis.

 - `http://localhost:8081/tickets` - API de gerenciamento de chamados
 - `http://localhost:8081/stats` - API de estatísticas para o relatório
   (também acessíveis via HTTPS em `https://localhost:8443/api/...`)
 - `api/create_ticket.php` - endpoint usado pela página de abertura de chamados

## Verificando o gateway

Para acompanhar as requisições encaminhadas pelo gateway, execute:

```bash
docker-compose logs -f gateway
```

Cada requisição gera uma linha de log indicando o método, a rota recebida e o serviço interno escolhido. Você também pode acessar `http://localhost:8081/` e verificar se a mensagem JSON apresenta os caminhos `/tickets` e `/stats`.

## Segurança e Manutenção

O projeto suporta login local ou via Amazon Cognito (arquivos `cognito_login.php` e `auth_callback.php`).

Um script `backup_db.sh` está disponível para gerar backups da base MySQL. Você pode agendar sua execução diária via cron. Há também o utilitário `sla_monitor.php` que dispara notificações antes do vencimento do SLA dos chamados.

Todos os acessos e ações relevantes são registrados na tabela `logs` do banco de dados, permitindo auditoria completa.


## Kubernetes

Os manifestos em `k8s/` definem Deployments e Services para cada microserviço.
Antes de aplicar, crie as imagens com as tags esperadas (o `docker-compose.yml` já define essas tags):

```bash
docker build -t web:latest -f Dockerfile .
docker build -t gateway:latest -f services/gateway/Dockerfile .
docker build -t tickets:latest -f services/tickets/Dockerfile .
docker build -t stats:latest -f services/stats/Dockerfile .
docker build -t nginx-proxy:latest -f nginx/Dockerfile nginx/
# ou simplesmente
docker-compose build
```

Se estiver utilizando o Minikube, carregue-as no cluster:

```bash
minikube image load web:latest
minikube image load gateway:latest
minikube image load tickets:latest
minikube image load stats:latest
minikube image load nginx-proxy:latest
```

Em seguida aplique os arquivos:

```bash
kubectl apply -f k8s/
```

Isso criará as instâncias `web`, `gateway`, `tickets`, `stats`, `db`, `phpmyadmin` e `nginx-proxy`. O banco de dados será populado pelo script `script_sql.sql` via ConfigMap.
O portal web e o gateway são expostos via NodePort (`30080` e `30081`), e o proxy HTTPS em `30443`. Para descobrir os endereços no Minikube, execute:

```bash
minikube service web
minikube service gateway
minikube service nginx-proxy
```

Ou encaminhe a porta manualmente:

```bash
kubectl port-forward service/web 8080:80
kubectl port-forward service/gateway 8081:80
kubectl port-forward service/nginx-proxy 8443:443
```
Depois acesse `http://localhost:8080` para o portal web e `http://localhost:8081` para o gateway.

Se algum pod ficar em `ImagePullBackOff`, verifique se as imagens estão disponíveis no Minikube com `minikube image ls`. Os manifestos definem `imagePullPolicy: Never` justamente para usar as imagens locais. Caso faltem, execute novamente `docker-compose build` e `minikube image load <nome>:latest` para cada serviço.

