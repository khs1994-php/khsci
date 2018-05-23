# Settings

## List

This returns a list of the settings for that repository. 

> 返回某个仓库的设置列表

| Method | URL                                                |
| :----- | :------------------------------------------------- |
| `GET`  | `/repo/{git_type}/{username}/{repo.name}/settings` |

**Example:** `GET` `/repo/github_app/khs1994-php/khsci/settings`

## Get

This returns a single setting.

> 返回某个设置

| Method | URL                                                              |
| :----- | :-------------------------------------------------------------   |
| `GET`  | `/repo/{git_type}/{username}/{repo.name}/setting/{setting.name}` |

## Update

This updates a single setting.

> 更新某个设置

```bash
$ curl -X PATCH \
    -H "Content-Type: application/json" \
    -H "KhsCI-API-Version: 3" \
    -H "Authorization: token xxxxxxxxxxxx" \
    -d '{ "setting.value": true }' \
    https://ci.khs1994.com/api/repo/github_app/khs1994-php/khsci/setting/{setting.name}
```

| Method   | URL                                                              |
| :-----   | :-------------------------------------------------------------   |
| `PATCH`  | `/repo/{git_type}/{username}/{repo.name}/setting/{setting.name}` |
