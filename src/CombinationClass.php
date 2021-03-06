<?php
namespace KeyWordGenerator;


// реализация класса
class Combination
{
    // свойства
    private $include = []; // array of string: слова, включаемые в запрос
    private $exclude = []; // array of string: слова, исключаемые из запроса


    // методы
    public function __construct(string $combination)
    {
        $combinationBuf = explode(" ", $combination); // array of Combination: содержит в себе комбинации

        // распределяем слова в группы
        foreach ($combinationBuf as &$word) {
            $word = explode(" ", self::fixWord($word));

            foreach ($word as &$w) {
                if (!empty($w)) {
                    if (mb_substr($w, 0, 1, "UTF-8") == "-") {
                        $this->exclude[] = mb_substr($w, 1, encoding:"UTF-8");
                    } else {
                        $this->include[] = $w;
                    }
                }
            }
        }
    }

    public static function fromData(array $include, array $exclude)
    {
        $obj = new Combination("");
        $obj->include = $include;
        $obj->exclude = $exclude;
        return $obj;
    }

    private function fixWord(string &$word) {
        // regexes
        $regFirst = '/[!+-]|[а-яёa-z]|\d/iu';
        $regNotFirst = '/[а-яёa-z]|\s|\d/iu';

        $fixedWord = "";

        // восстановление длины
        if (mb_strlen($word, "UTF-8") <= 2 && !(mb_strlen($word, "UTF-8") == 0)) {
            $fixedWord = '+' . $fixedWord;
        }

        // замена невалидных символов
        for ($i = 0; $i < mb_strlen($word, "UTF-8"); $i++) {
            if ($i == 0) {
                $isCorrect = preg_match($regFirst, mb_substr($word, $i, 1, "UTF-8"));
            }
            else {
                $isCorrect = preg_match($regNotFirst, mb_substr($word, $i, 1, "UTF-8"));
            }

            if ($isCorrect) {
                $fixedWord .= mb_substr($word, $i, 1, "UTF-8");
            } else {
                $fixedWord .= " ";
            }
        }

        return $fixedWord;
    }

    public function addInclude(array $elements) {
        foreach ($elements as &$element) {
            
            // режем слова, так как при удалении некорректного символа в середине
            // мы не учитываем образовавшиеся слова
            $words = explode(" ", $element);

            foreach ($words as &$word) {
            
                if (!in_array($word, $this->include)) {
                    if (!in_array($word, $this->exclude)) {
                        $this->exclude = array_diff($this->exclude, [$word]);
                    }
                    $this->include[] = $word;
                }
            }
        }
    }

    public function addExclude(array $elements) {
        foreach ($elements as &$element) {
            
            // режем слова, так как при удалении некорректного символа в середине
            // мы не учитываем образовавшиеся слова
            $words = explode(" ", $element);

            foreach ($words as &$word) {
            
                if (!in_array($word, $this->include) && !in_array($word, $this->exclude)) {
                    $this->exclude[] = $word;
                }
            }
        }
    }

    public function toString() // TEST ME
    {
        $string = array();

        foreach ($this->include as &$word) {
            $string[] = preg_replace('/\s/', '', $word);
        }

        foreach ($this->exclude as &$word) {
            $string[] = preg_replace('/\s/', '', "-" . $word);
        }

        return implode(" ", $string);
    }

    public function getInclude() // default, DON'T TEST
    {
        return $this->include;
    }

    public function getExclude() // default, DON'T TEST
    {
        return $this->exclude;
    }
}