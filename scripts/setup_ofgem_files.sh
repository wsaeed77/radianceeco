#!/usr/bin/env bash
# One-time setup script to upload Ofgem CSV files to shared storage on EC2
# Run this ONCE after deployment setup

set -euo pipefail

DEPLOY_PATH="/var/www/radiance"
SHARED_OFGEM="${DEPLOY_PATH}/shared/storage/ofgem_files"

echo "Setting up Ofgem files in shared storage..."

# Create directory if it doesn't exist
sudo mkdir -p ${SHARED_OFGEM}

echo ""
echo "Now upload your CSV files to the server using SCP:"
echo ""
echo "  scp storage/ofgem_files/eco4_partial_v6.csv ubuntu@your-server:~/ofgem_temp/"
echo "  scp storage/ofgem_files/gbis_partial_v3.csv ubuntu@your-server:~/ofgem_temp/"
echo "  scp \"storage/ofgem_files/ECO4 Full Project Scores Matrix.csv\" ubuntu@your-server:~/ofgem_temp/"
echo ""
echo "Then run:"
echo "  sudo mv ~/ofgem_temp/*.csv ${SHARED_OFGEM}/"
echo "  sudo chown -R www-data:www-data ${SHARED_OFGEM}"
echo "  sudo chmod -R 775 ${SHARED_OFGEM}"
echo "  rm -rf ~/ofgem_temp"
echo ""
echo "Files will be shared across all releases and only stored once!"

