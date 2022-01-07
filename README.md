# wmde-access

Tool showing an overview of WMDE employees and their access in various groups.

Visible @ https://wmde-access.toolforge.org

This tool is setup to automatically pull from this repository every 10 mins in a crontab.

```
*/10 * * * * /usr/bin/jsub -N cron-0 -once -quiet git -C /data/project/wmde-access/public_html pull
```