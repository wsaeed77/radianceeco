# GitHub Actions Deploy - Secrets & Setup

Add these repository secrets (Settings → Secrets and variables → Actions):

- SSH_HOST – SSH user and host, e.g. `ubuntu@your-domain.com` or `ubuntu@EC2_PUBLIC_IP`
- SSH_KEY – Private SSH key content that can connect to your EC2 (use a deploy key)
- SSH_PORT – Optional; default `22`
- DEPLOY_PATH – Server path, e.g. `/var/www/radiance`

## Server Prep (run once on EC2)

Run:

```
sudo mkdir -p /var/www/radiance/releases /var/www/radiance/shared
sudo chown -R ubuntu:ubuntu /var/www/radiance
# Put your .env in shared so future releases can symlink it
[ -f /var/www/radiance/current/.env ] && sudo cp /var/www/radiance/current/.env /var/www/radiance/shared/.env || true
```

Ensure nginx points to `/var/www/radiance/current/public`.

## Pipeline Flow
1. CI installs composer deps
2. CI builds frontend via `npm ci && npm run build`
3. CI rsyncs repo to `/releases/$GITHUB_SHA`
4. CI runs `scripts/deploy_remote.sh DEPLOY_PATH SHA`
5. Remote script: symlinks `.env`, runs migrations, caches, flips `current` symlink, restarts services

## Notes
- Node/NPM not required on EC2 (build happens in CI)
- `.env` stays on server under `shared/.env`
- Storage is per-release; for persistent user files, place under `shared` and symlink as needed
