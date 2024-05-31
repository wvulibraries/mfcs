# remove docker volumes
docker volume prune --all --force

# remove contents of ./data/logs
rm -rf ./data/logs/*

# remove ./data/exports contents and all subdirectories
rm -rf ./data/exports/*

# remove ./data/archives contents and all subdirectories
rm -rf ./data/archives/*

# remove ./data/working/uploads contents and all subdirectories
rm -rf ./data/working/uploads/*

# clear the ./data/working/tmp directory
rm -rf ./data/working/tmp/*
