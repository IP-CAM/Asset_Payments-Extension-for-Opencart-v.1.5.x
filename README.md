## Opentcart 1.5 payment extension

### Installation

* Backup your webstore and database
* Copy the content of [upload] directory to root of your website via ftp
* Activate and install the module in payment extensions (Extensions -> Payment)
* Press Edit button to configure settings:
  * Merchant Id
  * Secret key
  * Template ID (by default =19)
  * Order statuses for successful and failed payments
  * Specify region
  * Set the sort order 
  * Enable the module
  * Save settings
  
### Notes
Tested and developed with OpenCart v.1.5.6.1

## Модуль оплаты OpenCart 1.5

### Установка

* Создайте резервную копию вашего магазина и базы данных
* Загрузите содержимое папки upload в корень Вашего сайта по фтп 
* Установите модуль AssetPayments в разделе Модули -> Оплата
* Нажмите Редактировать и задайте в настройках модуля:
  * Id магазина
  * Ключ магазина
  * Id шаблона (по умолчанию =19)
  * Статусы заказа в случае успешной и не успешной оплаты
  * Активируйте модуль
  * Опционально задайте идентификатор модуля для сортировки его в списке способов оплаты. Меньшее значение подымает модуль вверх списка

### Примечания
Разработано и протестировано с OpenCart v.1.5.6.1
