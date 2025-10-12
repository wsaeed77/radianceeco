# 📦 Radiance CRM - Complete Deployment Package

## What's Included

This package contains everything you need to deploy Radiance CRM to your Ubuntu EC2 instance:

```
📁 Deployment Files
├── deploy.sh                  # System setup script
├── deploy-app.sh             # Application deployment script  
├── update.sh                 # Future updates script
├── QUICK_START_DEPLOYMENT.md # Quick start guide (START HERE!)
├── DEPLOYMENT_GUIDE.md       # Complete documentation
└── DEPLOYMENT_README.md      # This file
```

---

## 🎯 Choose Your Path

### Option 1: Quick & Easy (Recommended)
**For those who want to get up and running fast:**

Follow: **`QUICK_START_DEPLOYMENT.md`**

- Simple copy/paste commands
- Takes ~15 minutes
- Automated everything

### Option 2: Detailed & Customizable
**For those who want to understand every step:**

Follow: **`DEPLOYMENT_GUIDE.md`**

- Detailed explanations
- Troubleshooting tips
- Performance optimization
- Security hardening

---

## 📋 What You Need

### Before Starting:

1. ✅ **Ubuntu EC2 Instance** (20.04 or 22.04)
2. ✅ **SSH Key** to access your instance
3. ✅ **Security Group** configured:
   - Port 22 (SSH)
   - Port 80 (HTTP)  
   - Port 443 (HTTPS)
4. ⚪ **Domain Name** (optional, can use IP address)

### Information You'll Need:

- EC2 IP address or domain name
- Database password (create a strong one)
- EPC API key
- Admin email/password

---

## 🚀 Super Quick Start

```bash
# 1. SSH into your EC2
ssh -i your-key.pem ubuntu@YOUR-EC2-IP

# 2. Upload and extract your project
# (see QUICK_START_DEPLOYMENT.md for details)

# 3. Run deployment
sudo ./deploy.sh && sudo ./deploy-app.sh

# 4. Done! Visit http://YOUR-EC2-IP
```

---

## 📝 What the Scripts Do

### `deploy.sh` - System Setup
- ✅ Updates Ubuntu packages
- ✅ Installs PHP 8.1 + extensions
- ✅ Installs MySQL database
- ✅ Installs Node.js 18
- ✅ Installs Nginx web server
- ✅ Installs Composer
- ✅ Configures database

### `deploy-app.sh` - Application Deployment
- ✅ Installs PHP dependencies
- ✅ Installs Node.js dependencies
- ✅ Builds frontend assets
- ✅ Configures environment
- ✅ Runs database migrations
- ✅ Configures Nginx
- ✅ Sets up queue workers
- ✅ Configures cron jobs
- ✅ Optionally sets up SSL

### `update.sh` - Future Updates
- ✅ Pulls latest code
- ✅ Updates dependencies
- ✅ Runs migrations
- ✅ Rebuilds assets
- ✅ Restarts services
- ✅ Zero-downtime updates

---

## 🔒 Security Features

The deployment automatically includes:

- ✅ Firewall configuration (ufw)
- ✅ SSL certificates (Let's Encrypt)
- ✅ Secure file permissions
- ✅ MySQL security hardening
- ✅ Nginx security headers
- ✅ Environment file protection
- ✅ Hidden `.git` directory
- ✅ Rate limiting
- ✅ CSRF protection

---

## 📊 What Gets Installed

| Software | Version | Purpose |
|----------|---------|---------|
| Ubuntu | 20.04/22.04 | Operating System |
| PHP | 8.1 | Backend Runtime |
| MySQL | 8.0 | Database |
| Node.js | 18.x | Frontend Build |
| Nginx | Latest | Web Server |
| Composer | Latest | PHP Dependencies |
| Supervisor | Latest | Queue Management |
| Certbot | Latest | SSL Certificates |

---

## 🎨 Architecture

```
┌─────────────────────────────────────────────┐
│         Internet (Port 80/443)              │
└──────────────────┬──────────────────────────┘
                   │
           ┌───────▼────────┐
           │     Nginx      │  (Web Server)
           │  Reverse Proxy │
           └───────┬────────┘
                   │
       ┌───────────┴───────────┐
       │                       │
┌──────▼──────┐       ┌───────▼────────┐
│   PHP-FPM   │       │    Static      │
│  (Laravel)  │       │    Assets      │
└──────┬──────┘       └────────────────┘
       │
┌──────▼──────┐       ┌────────────────┐
│    MySQL    │       │   Supervisor   │
│  (Database) │       │ (Queue Worker) │
└─────────────┘       └────────────────┘
```

---

## 🔄 Deployment Flow

```
1. Run deploy.sh
   └─> Install system packages
   └─> Setup MySQL
   └─> Configure PHP
   └─> Install Node.js

2. Upload application code
   └─> Upload via SCP/Git
   └─> Extract to /var/www/radiance

3. Run deploy-app.sh
   └─> Install dependencies
   └─> Build assets
   └─> Configure .env
   └─> Run migrations
   └─> Setup Nginx
   └─> Start services

4. Application is live! 🎉
```

---

## 💾 Storage Structure

```
/var/www/radiance/          # Application root
├── app/                    # Laravel app code
├── public/                 # Web accessible files
│   └── build/             # Built assets
├── storage/               # Application storage
│   ├── app/              # Uploaded files
│   ├── logs/             # Log files
│   └── framework/        # Cache, sessions
├── database/             # Migrations, seeders
└── resources/            # Views, raw assets
```

---

## 📈 Performance

After deployment, your application will have:

- ✅ OPcache enabled (PHP acceleration)
- ✅ Route/config caching
- ✅ Asset minification
- ✅ Gzip compression
- ✅ Browser caching
- ✅ Database query optimization
- ✅ Queue processing (async tasks)

---

## 🐛 Common Issues & Solutions

### Issue: Can't SSH into EC2
**Solution:** Check Security Group allows port 22 from your IP

### Issue: "Permission denied"
**Solution:** Run scripts with `sudo`

### Issue: Scripts won't execute
**Solution:** `chmod +x deploy.sh deploy-app.sh update.sh`

### Issue: Website shows Nginx default page
**Solution:** Check Nginx config and restart:
```bash
sudo nginx -t
sudo systemctl restart nginx
```

### Issue: 500 Internal Server Error
**Solution:** Check permissions and logs:
```bash
sudo chown -R www-data:www-data /var/www/radiance
tail -f /var/www/radiance/storage/logs/laravel.log
```

---

## 📱 Mobile Responsive

The application is fully responsive and works on:
- 📱 Mobile phones
- 📱 Tablets
- 💻 Laptops
- 🖥️ Desktops

---

## 🎓 Learning Resources

- Laravel Docs: https://laravel.com/docs
- Inertia.js Docs: https://inertiajs.com
- React Docs: https://react.dev
- Nginx Docs: https://nginx.org/en/docs
- Ubuntu Server Guide: https://ubuntu.com/server/docs

---

## ✅ Post-Deployment Checklist

After deployment, verify:

- [ ] Application loads in browser
- [ ] Can log in with admin account
- [ ] Database connection works
- [ ] Can create/edit leads
- [ ] File uploads work
- [ ] Email sending works (if configured)
- [ ] EPC API integration works
- [ ] Google Drive integration works (if configured)
- [ ] Queue jobs processing
- [ ] Scheduler running
- [ ] SSL certificate installed (if using domain)
- [ ] Backups configured

---

## 🆘 Getting Help

1. **Check logs:**
   ```bash
   tail -f /var/www/radiance/storage/logs/laravel.log
   ```

2. **Restart services:**
   ```bash
   sudo systemctl restart nginx php8.1-fpm
   sudo supervisorctl restart radiance-worker:*
   ```

3. **Read documentation:**
   - `DEPLOYMENT_GUIDE.md` for detailed info
   - `QUICK_START_DEPLOYMENT.md` for quick commands

4. **Common commands:**
   ```bash
   # Check service status
   sudo systemctl status nginx
   sudo systemctl status php8.1-fpm
   sudo systemctl status mysql
   
   # View logs
   sudo tail -f /var/log/nginx/error.log
   sudo journalctl -u nginx -f
   
   # Clear caches
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

---

## 🎉 You're All Set!

Your Radiance CRM is now deployed and ready to use!

**Next Steps:**
1. Create admin user account
2. Configure EPC API key
3. Test all features
4. Set up backups
5. Configure domain (if not done)
6. Invite your team members

---

**Deployment Package Version:** 1.0  
**Last Updated:** October 2025  
**Compatible With:** Ubuntu 20.04, 22.04

