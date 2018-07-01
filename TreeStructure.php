<?php

/*
    TreeStructure
    Copyright © 2018 Podvirnyy Nikita (KRypt0n_)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.

    and

    Enfesto Studio Group license
    https://vk.com/topic-113350174_36400959

    -----------------------

    Contacts:

    Email: <suimin.tu.mu.ga.mi@gmail.com>
    VK:    vk.com/technomindlp
           vk.com/hphp_convertation
*/

class Tree
{
    protected $tree; // Дерево

    /*
        @tree - Дерево (получать через метод getTree)
    */

    public function __construct ($tree = array ()) // Создание дерева
    {
        $this->tree = $tree;
    }

    /*
        @value - Элемент для добавления
    */

    public function push ($value, $index = null) // Добавление информации в дерево
    {
        if (is_array ($value)) // Обрабатываем массив элементов
        {
            $ans = true;

            foreach ($value as $id => $val)
                if (self::push ($val) === false)
                    $ans = false;

            return $ans;
        }

        else // Обрабатываем элемент
        {
            $level = 0; // Текущий уровень вложенности
            $point = 0; // Текущий узел

            while ($this->tree[$level]) // Пока существует текущий уровень
            {
                if ($value < $this->tree[$level][$point]) // Если элемент меньше сохранённого
                    $point = 2 * $point; // То мы идём налево

                elseif ($value > $this->tree[$level][$point]) // Если элемент больше сохранённого
                    $point = 2 * $point + 1; // То мы идём направо

                elseif ($this->tree[$level][$point] === $value) // Иначе (умное)
                    return false; // Возвращаем ошибку т.к. элемент уже есть в дереве

                $level++; // Идём дальше
            }

            $this->tree[$level][$point] = $value; // Добавляем элемент в дерево (т.к. нужные параметры нам уже известны)

            return true;
        }
    }

    /*
        @value - Элемент для удаления
    */

    public function pop ($value) // Удаление информации из дерева
    {
        if (is_array ($value)) // Обрабатываем массив элементов
        {
            $ans = true;

            foreach ($value as $id => $val)
                if (self::pop ($val) === false)
                    $ans = false;

            return $ans;
        }
        
        else // Обрабатываем элемент
        {
            $level = 0; // Текущий уровень вложенности
            $point = 0; // Текущий узел

            while ($this->tree[$level]) // Пока существует текущий уровень
            {
                if ($value < $this->tree[$level][$point]) // Если элемент меньше сохранённого
                    $point = 2 * $point; // То мы идём налево

                elseif ($value > $this->tree[$level][$point]) // Если элемент больше сохранённого
                    $point = 2 * $point + 1; // То мы идём направо

                elseif ($this->tree[$level][$point] === $value) // Иначе (умное) мы нашли нужный нам элемент
                {
                    unset ($this->tree[$level][$point]); // Удаляем элемент из дерева

                    self::rebuildTree ($layer); // Пересобираем дерево

                    return true;
                }

                $level++; // Идём дальше
            }

            return false;
        }
    }

    /*
        @value - Элемент для поиска
    */

    public function search ($value)
    {
        $level = 0; // Текущий уровень вложенности
        $point = 0; // Текущий узел

        while ($this->tree[$level]) // Пока существует текущий уровень
        {
            if ($value < $this->tree[$level][$point]) // Если элемент меньше сохранённого
                $point = 2 * $point; // То мы идём налево

            elseif ($value > $this->tree[$level][$point]) // Если элемент больше сохранённого
                $point = 2 * $point + 1; // То мы идём направо

            elseif ($this->tree[$level][$point] === $value) // Иначе (умное) мы нашли нужный нам элемент
                return "$level:$point"; // Выводим его путь

            $level++; // Идём дальше
        }

        return false;
    }

    /*
        @path  - Путь до узла (получать через метод search)
        @value - Элемент для вставки
    */

    public function set ($path, $value) // Установить значение в дереве
    {
        $this->tree[current ($exp = explode (':', $path))][end ($exp)] = $value;
    }

    /*
        @path - Путь до узла (получать через метод search)
    */

    public function get ($path) // Получить значение из дерева
    {
        return $this->tree[current ($exp = explode (':', $path))][end ($exp)];
    }

    /*
        @path - Путь до узла (получать через метод search)
    */

    public function remove ($path) // Удалить значение из дерева
    {
        unset ($this->tree[current ($exp = explode (':', $path))][end ($exp)]);

        self::rebuildTree ($exp[0]); // Пересобираем дерево
    }

    /*
        [@beginIndex = 0] - Уровень, с которого начинается пересборка дерева
    */

    public function rebuildTree ($beginIndex = 0) // Пересобрать дерево
    {
        # Получаем все элементы дерева по заданному индексу, удаляем их и отправляем как запрос на добавление
        self::push (self::parsValuesInArray ($this->tree, $beginIndex, true));
    }

    public function getTree () // Получить дерево
    {
        return $this->tree;
    }

    /*
        @array - Массив для получения элементов
        @index - Начальный индекс
        @erase - Удалять ли элементы из массива
    */

    protected function parsValuesInArray (&$array, $index, $erase = false) // Получение всех значений на всех подмассивов массива начиная с какого-то индекса
    {
        $end    = count ($array); // Конец массива
        $return = array ();       // Вывод

        for ($index; $index < $end; $index++) // Идём от $index до $end
        {
            if (is_array ($array[$index])) // Проверка...
            {
                if (!is_array ($values = array_values ($array[$index]))) // Если не массив, то
                    $values = array (); // Делаем массивом... ;D

                $return = array_merge ($return, $values); // Добавляем элементы к выводу
            }

            if ($erase) // Если надо - ...
                unset ($array[$index]); // Удаляем!
        }

        return $return; // Возаращаем элементы
    }
}

?>
