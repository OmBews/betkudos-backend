<?php

return [
    "accepted" => "O atributo deve ser aceito.",
    "active_url" => "O atributo não é um URL válido.",
    "after" => "O atributo deve ser uma data após: data.",
    "after_or_equal" => "O atributo deve ser uma data após ou igual a: data.",
    "alpha" => "O atributo só pode conter letras.",
    "alpha_dash" => "O atributo pode conter apenas letras, números, traços e sublinhados.",
    "alpha_num" => "O atributo pode conter apenas letras e números.",
    "array" => "O atributo deve ser uma matriz.",
    "base64_image" => "A imagem fornecida deve ser uma imagem de base 64 válida com: dimensões",
    "before" => "O atributo deve ser uma data antes: data.",
    "before_or_equal" => "O atributo deve ser uma data antes ou igual a: data.",
    "between" => [
        "array" => "O atributo deve ter entre: min e: itens max.",
        "file" => "O atributo deve estar entre: min e: Max Kilobytes.",
        "numeric" => "O atributo deve estar entre: min e: max.",
        "string" => "O atributo deve estar entre: min e: max caracteres."
    ],
    "boolean" => "O campo: Atributo deve ser verdadeiro ou falso.",
    "confirmed" => "A confirmação: Atributo não corresponde.",
    "custom" => [
        "password" => [
            "lowercase" => "A senha deve incluir pelo menos uma minúscula",
            "matches_username" => "A senha não deve ser muito semelhante ao seu nome de usuário",
            "number" => "A senha deve incluir pelo menos um número",
            "uppercase" => "A senha deve incluir pelo menos uma maiúscula"
        ]
    ],
    "date" => "O atributo não é uma data válida.",
    "date_equals" => "O atributo deve ser uma data igual a: data.",
    "date_format" => "O atributo não corresponde ao formato: formato.",
    "different" => "O atributo e: outro deve ser diferente.",
    "digits" => "O atributo deve ser: dígitos dígitos.",
    "digits_between" => "O atributo deve estar entre: min e: max dígitos.",
    "dimensions" => "O atributo tem dimensões inválidas de imagem.",
    "distinct" => "O campo: Atributo tem um valor duplicado.",
    "email" => "O atributo deve ser um endereço de e-mail válido.",
    "ends_with" => "O atributo deve terminar com um dos seguintes :: Valores",
    "exists" => "O atributo selecionado: é inválido.",
    "file" => "O atributo deve ser um arquivo.",
    "filled" => "O campo: Atributo deve ter um valor.",
    "gt" => [
        "array" => "O atributo deve ter mais de: itens de valor.",
        "file" => "O atributo deve ser maior que: valor kilobytes.",
        "numeric" => "O atributo deve ser maior que: valor.",
        "string" => "O atributo deve ser maior que: caracteres de valor."
    ],
    "gte" => [
        "array" => "O atributo deve ter: itens de valor ou mais.",
        "file" => "O atributo deve ser maior ou igual: valor kilobytes.",
        "numeric" => "O atributo deve ser maior ou igual: valor.",
        "string" => "O atributo deve ser maior ou igual: caracteres de valor."
    ],
    "image" => "O atributo deve ser uma imagem.",
    "in" => "O atributo selecionado: é inválido.",
    "in_array" => "O campo: Atributo não existe em: Outro.",
    "integer" => "O atributo deve ser um inteiro.",
    "ip" => "O atributo deve ser um endereço IP válido.",
    "ipv4" => "O atributo deve ser um endereço IPv4 válido.",
    "ipv6" => "O atributo deve ser um endereço IPv6 válido.",
    "json" => "O atributo deve ser uma string JSON válida.",
    "lt" => [
        "array" => "O atributo deve ter menos de: itens de valor.",
        "file" => "O atributo deve ser menor que: valor kilobytes.",
        "numeric" => "O atributo deve ser menor que: valor.",
        "string" => "O atributo deve ser menor que: caracteres de valor."
    ],
    "lte" => [
        "array" => "O atributo não deve ter mais de: itens de valor.",
        "file" => "O atributo deve ser menor ou igual: valor kilobytes.",
        "numeric" => "O atributo deve ser menor ou igual: valor.",
        "string" => "O atributo deve ser menor ou igual: caracteres de valor."
    ],
    "max" => [
        "array" => "O atributo pode não ter mais do que: itens max.",
        "file" => "O atributo pode não ser maior que: Max Kilobytes.",
        "numeric" => "O atributo pode não ser maior que: max.",
        "string" => "O atributo pode não ser maior que: caracteres max."
    ],
    "mimes" => "O atributo deve ser um arquivo de tipo:: valores.",
    "mimetypes" => "O atributo deve ser um arquivo de tipo:: valores.",
    "min" => [
        "array" => "O atributo deve ter pelo menos: itens mínimos.",
        "file" => "O atributo deve ser pelo menos: Min Kilobytes.",
        "numeric" => "O atributo deve ser pelo menos: min.",
        "string" => "O atributo deve ser pelo menos: caracteres min."
    ],
    "not_in" => "O atributo selecionado: é inválido.",
    "not_regex" => "O formato de atributo é inválido.",
    "numeric" => "O atributo deve ser um número.",
    "password" => "A senha está incorreta.",
    "present" => "O campo: Atributo deve estar presente.",
    "regex" => "O formato de atributo é inválido.",
    "required" => "O campo: Atributo é necessário.",
    "required_if" => "O campo: Atributo é necessário quando: outro é: valor.",
    "required_unless" => "O campo: Atributo é necessário, a menos que: outro esteja em: valores.",
    "required_with" => "O campo: Atributo é necessário quando: os valores estão presentes.",
    "required_with_all" => "O campo: Atributo é necessário quando: os valores estão presentes.",
    "required_without" => "O campo: Atributo é necessário quando: os valores não estão presentes.",
    "required_without_all" => "O campo: Atributo é necessário quando nenhum dos: valores estão presentes.",
    "same" => "O: Atributo e: Outro deve corresponder.",
    "size" => [
        "array" => "O atributo deve conter: itens de tamanho.",
        "file" => "O atributo deve ser: kilobytes de tamanho.",
        "numeric" => "O atributo deve ser: tamanho.",
        "string" => "O atributo deve ser: caracteres de tamanho."
    ],
    "starts_with" => "O atributo deve começar com um dos seguintes :: Valores",
    "string" => "O atributo deve ser uma string.",
    "timezone" => "O atributo deve ser uma zona válida.",
    "unique" => "O atributo já foi realizado.",
    "uploaded" => "O atributo falhou ao carregar.",
    "url" => "O formato de atributo é inválido.",
    "uuid" => "O atributo deve ser um UUID válido."
];
