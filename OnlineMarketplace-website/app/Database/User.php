<?php

namespace App\Database;

use App\Exceptions\UserException;
use App\Facades\Hash;
use App\Facades\Response;
use Exception;
use PDO;

class User extends Database
{
    protected static string $get_all_users = "SELECT * FROM users;";
    protected static string $get_by_id = "SELECT * FROM users WHERE `id` = {id};";
    protected static string $delete_user = "DELETE FROM users WHERE `id` = {id};";
    protected static string $update_user = "UPDATE users SET {sets} WHERE `id` = {id};";
    protected static string $check_user = "SELECT * FROM users WHERE `email` = '{email}';";
    protected static string $create_user = "INSERT INTO users ({columns}) VALUES ({values});";


    public function __construct()
    {
        parent::__construct();
    }

    public static function all(): array|bool
    {
        new self;

        $sql = self::$get_all_users;

        try {
            $stmt = self::$db->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            return UserException::error($e->getMessage());
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_by_id(int $id): array|bool
    {
        $sql = self::setId(self::$get_by_id, $id);

        try {
            $stmt = self::$db->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            return UserException::error($e->getMessage());
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function delete(int $id): string
    {
        new self;
        $sql = self::$delete_user;
        $sql = self::setId($sql, $id);

        $stmt = self::$db->prepare($sql);

        try {
            if ($stmt->execute()) return Response::success("user deleted successfuly");
        } catch (Exception $e) {
            return UserException::error($e->getMessage());
        }
    }

    public static function update(int $id, array $data): array|bool
    {
        new self;

        $sql = self::setId(self::$update_user, $id);
        $sql = self::setParams($sql, $data);


        try {
            $stmt = self::$db->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            return UserException::error($e->getMessage());
        }

        return self::get_by_id($id);
    }

    public static function check(string $email, string $password): bool
    {
        new self;

        $sql = self::$check_user;
        $sql = self::setEmail($sql, $email);

        $stmt = self::$db->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            UserException::error($e->getMessage(), 500);
        }

        if ($stmt->rowCount() == 0) UserException::error("something went wrong in your credentials", 403);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return self::_check($user, $email, $password);
    }

    protected static function setEmail(string $sql, string $email): string
    {
        return str_replace("{email}", $email, $sql);
    }

    private static function _check(array $user, string $email, string $password): bool
    {
        if ($user["email"] == $email && Hash::verify($password, $user["password"])) {
            return true;
        }
        return false;
    }

    public static function create(array $data): array
    {
        new self;

        unset($data["id"]);
        $sql = self::$create_user;
        $sql = self::setColumns($sql, array_keys($data));
        $sql = self::setValues($sql, array_values($data));

        try {
            $stmt = self::$db->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            return UserException::error($e->getMessage());
        }

        return self::get_by_id(self::$db->lastInsertId());
    }
}
