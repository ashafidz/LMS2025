## linux ubuntu development setup :

- menggunakan docker/podman untuk service database development
- pakai php dan composer installed natively
- nodejs via nvm

### pastikan pakai php versi terbaru dan tools tools standard laravel

```
# 1. Tambahkan repository PHP jika belum ada
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# 2. Instal PHP 8.4 dan extension yang dibutuhkan Laravel
sudo apt install php8.4 php8.4-cli php8.4-common php8.4-xml php8.4-mbstring php8.4-curl php8.4-zip php8.4-mysql php8.4-sqlite3 php8.4-gd php8.4-bcmath php8.4-intl -y

# 3. Ubah default CLI sistem ke PHP 8.4
sudo update-alternatives --set php /usr/bin/php8.4

```

### install composer

```
wget https://getcomposer.org/composer.phar
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
composer --version
```

### install nodejs

```
curl -o- https://github.com/nvm-sh/nvm | bash
source ~/.bashrc
nvm install 22
nvm use 22
nvm alias default 22
```

### setup backend:

```
cd backend
composer install
php artisan key:generate
cp .env.example .env // sesuakan cred db dengan docker-compose.yml
docker compose up -d --force-recreate //ganti port apabila port sudah terpakai dan sesuaikan kembali .env
php artisan migrate
php artisan db:seed        # role (termasuk role AMI & RKK), master data, user default
php artisan serve
```

### setup frontend:

```
npm install
npm run dev
```

### access default
```
http://localhost:8000/ project
http://localhost:8025/ mailhog
```
