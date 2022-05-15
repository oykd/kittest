<?php

use Router\Router;
use DB\MySQL as DB;
use Models\Admin;

/**
 * Class Application
 */
final class Application
{
    /**
     * Application constructor.
     */
    public function __construct()
    {
        // здесь можно произвести инъекцию зависимостей
    }

    /**
     * Устанавливаем таблицы и пользователя
     * @throws \Exceptions\AdminException | \Exceptions\DBException
     */
    public static function install()
    {
        // проверям таблицы, которые следует установить
        foreach (glob(ROOT . "/configs/*.sql") as $sql) {
            $table = pathinfo($sql, PATHINFO_FILENAME);
            if (!DB::get()->exists($table)) {
                $query = file_get_contents($sql);
                DB::query($query);
            }
        }

        // добавляем дефолтного пользователя, если таблица пустая
        if (!DB::query("SELECT * FROM `" . Admin::table . "`")->first()) {
            Admin::get()->register('admin', '12345');
        }
    }

    /**
     * Шаблон выполнения
     */
    public function run()
    {
        // Заключаем все в блок TRY, чтобы отлавливать возникающие исключения в одном месте
        // Исключения разделены по типам, чтобы можно было реагировать на них по-разному:
        // выводить, логировать, перенаправлять на другую страницу и тд
        try {
            // Включаем буферизацию вывода
            // * Буферизация нужна, чтобы в случае возникновения непредвиденной ошибки отменить вывод
            ob_start();

            // Соединяемся с БД
            DB::connect(require_once ROOT . "/configs/database.conf.php");

            // Устанавливаем необходимые таблицы и пользователя, если их нет
            self::install();

            // Инициализируем маршрутизатор
            Router::init(require_once ROOT . "/configs/routes.conf.php");

            // Инициализируем сессию логина пользователя
            $admin = Admin::init();

            // Запускаем маршрутизатор.
            // Маршрутизатор выберет контроллер в зависимости от запроса и роли пользователя,
            // а контроллер выполнит необходимые действия либо вызовет исключение
            // * в данной задаче у нас всего две роли: admin и guest
            Router::get()->launch($admin ? 'admin' : 'guest');

        } catch (\Exceptions\DBException $e) {

            // Исключения связанные с БД
            ob_clean();
            echo $e->getMessage();

        } catch (\Exceptions\RouterException $e) {

            // Исключения маршрутизатора (access denied, not found)
            ob_clean();
            echo $e->getMessage();

        } catch (\Exceptions\AdminException $e) {

            // Исключения логина и регистрации
            ob_clean();
            echo json_encode([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        } catch (\Exceptions\TreeException $e) {

            // Исключения дерева
            ob_clean();
            echo json_encode([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        } catch (\Exception $e) {

            // Неизвестные ошибки
            ob_clean();
            echo $e->getMessage();

        } finally {

            // выводим буфер
            ob_get_contents();

        }
    }
}