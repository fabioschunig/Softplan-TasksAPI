# 🚀 Deploy HostGator - Opção 1 (Simples)

## 📋 Resumo
- **Raiz do domínio**: `public/` (contém APIs + React)
- **APIs**: Funcionam normalmente (sem mudanças)
- **React**: Servido via `public/index.php`

## 🛠️ Passo a Passo

### 1. **Preparar localmente**
```bash
# Na raiz do projeto
chmod +x deploy-to-public.sh
./deploy-to-public.sh
```

### 2. **Configurar .env do React**
Edite `react-app/.env`:
```bash
REACT_APP_API_BASE_URL=
REACT_APP_ENV=production
```

### 3. **Configurar .env do Backend**
Edite `config/.env` com dados da HostGator:
```bash
DB_HOST=localhost
DB_DBNAME=seuusuario_tasks
DB_USERNAME=seuusuario_admin
DB_PASSWORD=suasenha
```

### 4. **Upload para HostGator**
Faça upload das seguintes pastas para a **raiz do seu domínio**:

```
public/          → Raiz do domínio
├── index.php    → Serve o React
├── app/         → Arquivos do React (build)
├── *.api.php    → APIs PHP
└── .htaccess    → Configurações

src/             → Backend PHP
vendor/          → Dependências Composer  
config/          → Configurações (.env)
```

## 🌐 **URLs Finais**
- `https://seudominio.com/` → React App
- `https://seudominio.com/auth.api.php` → API Login
- `https://seudominio.com/task.api.php` → API Tasks

## ✅ **Vantagens desta solução**
- ✅ **Zero mudanças** nas APIs PHP
- ✅ **URLs relativas** no React
- ✅ **Mesmo domínio** (sem CORS)
- ✅ **Setup simples**

## 🔧 **Troubleshooting**

### React não carrega?
1. Verifique se `public/app/index.html` existe
2. Acesse `https://seudominio.com/index.php` diretamente

### APIs não funcionam?
1. Teste `https://seudominio.com/auth.api.php?action=validate`
2. Verifique logs de erro no cPanel

### Banco não conecta?
1. Confirme dados no `config/.env`
2. Use o script de teste: `test-connection.php`

## 📞 **Suporte**
Se algo não funcionar, verifique:
1. Arquivo `config/.env` com dados corretos da HostGator
2. Pasta `vendor/` foi enviada
3. Permissões dos arquivos PHP (644 ou 755)
