#!/bin/bash
SCIEZKA="../firmy/$1"

if [ -d "$SCIEZKA" ]; then
    echo "Taki folder już istnieje!"
    exit 1
fi

mkdir -p "$SCIEZKA" || { echo "Nie można było utworzyć folderu"; exit 1; }

cp -r ./aplikacja/* "$SCIEZKA" || { echo "Nie można było skopiować aplikacji"; exit 1; }

if [ ! -d "$SCIEZKA/public" ]; then
    echo "Nie można było skopiować aplikacji"
    exit 1
fi

echo "$2" > "$SCIEZKA/.env" || { echo "Nie można było stworzyć env"; exit 1; }

composer install --working-dir="$SCIEZKA" || { echo "Nie można było utworzyć folderu vendor"; exit 1; }

php $SCIEZKA/bin/console doctrine:migrations:migrate

echo "Deployment zakończony sukcesem!"
