## 开发环境

本项目基于`Laravel 5.8+`，需要有一定的`Laravel`框架的使用经验。详见[Laravel中文文档](https://learnku.com/docs/laravel/5.8)

**系统需求**

* PHP 7.1+
* Composer
* Redis 
* Elasticsearch 6.0+ (ik分词)
* Supervisor
* Kibana

克隆本仓库后运行`composer install`安装依赖组件。依赖安装完成之后将`.env.example`复制为`.env`并根据当前的开发环境进行配置。

配置好之后运行`php artisan key:generate`，生成一个随机APP_KEY





##API接口文档
### 一、鉴权（所有接口都需要鉴权）
1、加密key
>接口加密所需秘钥为custom_appid 和 custom_appkey

2、签名算法

```
签名生成的通用步骤如下：
第一步，组合加密所需的集合M，加密集合M有以下参数组成:
[X-API-KEY:{custom_appid},X-API-TIMESTAMP:时间戳,X-API-NONSTR:6位随机数,Accept: application/vnd.log.v1+json]
第二步，将集合M内非空参数值的参数按照参数名ASCII码从小到大排序（字典序），使用URL键值对的格式（即key1=value1&key2=value2…）拼接成字符串stringA。

特别注意以下重要规则：

◆ 参数名ASCII码从小到大排序（字典序）；
◆ 如果参数的值为空不参与签名；
◆ 参数名区分大小写；
◆ 传送的X-API-SINGATURE参数不参与签名。

第三步，在stringA最后拼接上key得到stringSignTemp字符串，并对stringSignTemp进行sha256加密运算，再将得到的字符串所有字符转换为大写，得到X-API-SINGATURE值。

```   

3、加密样例

```
假设custom_appid 为 `demo1`，custom_appkey 为 `demo1-key`,请求的接口版本号为`v1`，当前时间戳为`1561599670`，随机数为`tebxte`

那么 Header签名集合M为：
X-API-KEY : demo1
X-API-TIMESTAMP : 1561599670
Accept : application/vnd.log.v1+json
X-API-NONSTR : tebxte

接下来获取签名字符串stringSignTemp:
首先将集合M去空排列得到
StringA :  Accept=application/vnd.log.v1+json&X-API-KEY=demo1&X-API-NONSTR=tebxte&X-API-TIMESTAMP=1561600119
那么：
stringSignTemp : Accept=application/vnd.esearch.v1+json&X-API-KEY=demo1&X-API-NONSTR=tebxte&X-API-TIMESTAMP=1561600119&key=demo1-key

最后：
X-API-SIGNATURE : strtoupper(hash_hmac('sha256', stringSignTemp, 'demo1-key'));
```



### 二、日志入库接口
1、接口地址   

```
【POST】{server_host}/api/logs/add
```

2、参数   

| 参数名 | 参数类型 | 参数说明 | 备注 |
| --- | --- | --- | --- |
| sys_bundle | string | 系统标识 | 必填 |
| app_bundle | string | 应用标识 | 必填 |
| module_bundle | string | 模块标识 | 选填，不填默认与app_bundle相同 |
| op | string | 操作标识 | 必填 |
| op_name | string | 操作标识描述 | 选填 |
| user_id | string | 用户ID | 选填 |
| user_name | string | 用户名 | 选填 |
| timing | string | 操作耗时 | 选填 |
| create_time | string | 时间戳 | 选填，不填默认为当前时间 |
| analysis | string | 附加信息 | 选填 |

3、返回值   

| 参数名 | 参数类型 | 参数说明 |
| --- | --- | --- |
| code | string | 200为正常返回，400为报错 |
| message | string | code的具体描述 |
| id | string | ESearch id，code为200时返回 |

```php
{
    "code": 200,
    "message": "添加成功",
    "id": "8e1f91e8-a81b-4fd7-b671-a9d237cfef5c"
}
```



### 三、日志搜索接口

1、接口地址   

```
【GET】{server_host}/api/logs/search
```

2、参数   

| 参数名 | 参数类型 | 参数说明 | 备注 |
| --- | --- | --- | --- |
| offset | int | 偏移量 | 选填，不填默认为0 |
| count | int | 数据量 | 选填，不填默认为10 |
| sort_field | string | 排序字段 | 可选，默认为_score |
| sort_type | string | 排序方式 | 可选，默认为desc |
| search_field | string | 搜索字段名 | 必填，多个用@@隔开，eg：user\_name@@op\_name |
| search_value | string | 搜索字段对应的值 | 必填,多个用@@隔开，eg：丁@@审核文稿 |


3、返回值   

| 参数名 | 参数类型 | 参数说明 |
| --- | --- | --- |
| code | string | 200为正常返回，400为报错 |
| data | string | es命中数据 |
| data.total | string | 符合条件的数据总数 |
| data.hits | string | 符合条件的记录 |

```php
{
    "code": 200,
    "data": {
        "total": 2,
        "max_score": 0.25316024,
        "hits": [
            {
                "_index": "logs",
                "_type": "logs",
                "_id": "8e1da748-2af8-4d72-a62c-f7217a41e12a",
                "_score": 0.25316024,
                "_source": {
                    "sys_bundle": "plus",
                    "app_bundle": "news",
                    "module_bundle": "news",
                    "op": "create",
                    "op_name": "创建文稿-如何呵呵呵",
                    "user_id": "110",
                    "user_name": "丁正杰",
                    "create_time": 1562580117,
                    "analysis": "people are sports hi",
                    "ip": "127.0.0.1"
                }
            },
            {
                "_index": "logs",
                "_type": "logs",
                "_id": "8e1da755-05c0-40ee-9203-6e0f9b81712b",
                "_score": 0.25316024,
                "_source": {
                    "sys_bundle": "plus",
                    "app_bundle": "news",
                    "module_bundle": "news",
                    "op": "create",
                    "op_name": "创建文稿-如何呵呵呵",
                    "user_id": "110",
                    "user_name": "张正",
                    "create_time": 1562580125,
                    "analysis": "people are sports hi",
                    "ip": "127.0.0.1"
                }
            }
        ]
    }
}
```









