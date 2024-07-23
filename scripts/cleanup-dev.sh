# remove docker volumes
docker volume prune --all --force

# remove contents of ./data/logs
rm -rf ./data/logs/*

# remove ./data/exports contents and all subdirectories
rm -rf ./data/mfcs-data/exports/*

# remove ./data/archives contents and all subdirectories
rm -rf ./data/mfcs-data/archives/*

# remove ./data/working/uploads contents and all subdirectories
rm -rf ./data/mfcs-data/working/uploads/*

# clear the ./data/working/tmp directory
rm -rf ./data/mfcs-data/working/tmp/*
