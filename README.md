# wmde-access

Tool showing an overview of WMDE employees and their access in various groups.

Visible @ https://wmde-access.toolforge.org

This tool is setup to automatically pull from this repository every 10 mins in a crontab.

```
*/10 * * * * /usr/bin/jsub -N cron-0 -once -quiet git -C /data/project/wmde-access/public_html pull
```

## Development

Might use provided docker compose file to develop the tool locally.

Running `docker-compose up -d` should install PHP dependencies, and run the tool on `localhost:8080`.

Note: You might need to change the owner of the `cache` directory to the user you are running docker compose commands with, otherwise nginx process might not be able to create/update cache files.
`chown $(id -u):$(id -g) cache`
