# wmde-access

Tool showing an overview of WMDE employees and their access in various groups.

Visible @ https://wmde-access.toolforge.org

The tool is set up to automatically pull from this repository every 10 minutes.

```sh
toolforge-jobs run git-pull --command 'git -C ~/public_html pull' --image tf-php74 --schedule '*/10 * * * *'
```
