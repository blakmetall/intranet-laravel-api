<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    /*
     *
     * */

    'accepted'             => 'El :attribute debe ser aceptado.',
    'active_url'           => 'El :attribute no es una URL válida.',
    'after'                => 'El :attribute debe ser una fecha posterior a :date.',
    'alpha'                => 'El :attribute solo debe contener letras.',
    'alpha_dash'           => 'El :attribute solo debe contener letras, números y guiones.',
    'alpha_num'            => 'El :attribute solo debe contener letras y números.',
    'array'                => 'El :attribute debe ser un conjunto.',
    'before'               => 'El :attribute debe ser una fecha anterior a :date.',
    'before_or_equal'      => 'El :attribute debe ser una fecha anterior o igual a :date.',
    'between'              => [
        'numeric' => 'El :attribute tiene que estar entre :min - :max.',
        'file'    => 'El :attribute debe pesar entre :min - :max kilobytes.',
        'string'  => 'El :attribute tiene que tener entre :min - :max caracteres.',
        'array'   => 'El :attribute tiene que tener entre :min - :max ítems.',
    ],
    'boolean'              => 'El :attribute debe tener un valor verdadero o falso.',
    'confirmed'            => 'La confirmación de :attribute no coincide.',
    'date'                 => 'El :attribute no es una fecha válida.',
    'date_format'          => 'El :attribute no corresponde al formato :format.',
    'different'            => 'El :attribute y :other deben ser diferentes.',
    'digits'               => 'El :attribute debe tener :digits dígitos.',
    'digits_between'       => 'El :attribute debe tener entre :min y :max dígitos.',
    'distinct'             => 'El :attribute contiene un valor duplicado.',
    'email'                => 'El :attribute no es un correo válido',
    'exists'               => 'El :attribute es inválido.',
    'file'                 => 'El :attribute debe ser un archivo.',
    'filled'               => 'El :attribute es obligatorio.',
    'image'                => 'El :attribute debe ser una imagen.',
    'in'                   => 'El :attribute es inválido.',
    'in_array'             => 'El :attribute no existe en :other.',
    'integer'              => 'El :attribute debe ser un número entero.',
    'ip'                   => 'El :attribute debe ser una dirección IP válida.',
    'ipv4'                 => 'El :attribute debe ser una dirección IPv4 válida.',
    'ipv6'                 => 'El :attribute debe ser una dirección IPv6 válida.',
    'json'                 => 'El :attribute debe tener una cadena JSON válida.',
    'max'                  => [
        'numeric' => 'El :attribute no debe ser mayor a :max.',
        'file'    => 'El :attribute no debe ser mayor que :max kilobytes.',
        'string'  => 'El :attribute no debe ser mayor que :max caracteres.',
        'array'   => 'El :attribute no debe tener más de :max elementos.',
    ],
    'mimes'                => 'El :attribute debe ser un archivo con formato: :values.',
    'mimetypes'            => 'El :attribute debe ser un archivo con formato: :values.',
    'min'                  => [
        'numeric' => 'El :attribute debe ser de al menos :min.',
        'file'    => 'El :attribute debe ser de al menos :min kilobytes.',
        'string'  => 'El :attribute debe contener al menos :min caracteres.',
        'array'   => 'El :attribute debe tener al menos :min elementos.',
    ],
    'not_in'               => ':attribute es inválido.',
    'numeric'              => ':attribute debe ser numérico.',
    'present'              => 'El :attribute debe estar presente.',
    'regex'                => 'El :attribute es inválido.',
    'required'             => 'El :attribute es obligatorio.',
    'required_if'          => 'El :attribute es obligatorio cuando :other es :value.',
    'required_unless'      => 'El :attribute es obligatorio a menos que :other esté en :values.',
    'required_with'        => 'El :attribute es obligatorio cuando :values está presente.',
    'required_with_all'    => 'El :attribute es obligatorio cuando :values está presente.',
    'required_without'     => 'El :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El :attribute es obligatorio cuando ninguno de :values estén presentes.',
    'same'                 => ':attribute y :other deben coincidir.',
    'size'                 => [
        'numeric' => 'El tamaño de :attribute debe ser :size.',
        'file'    => 'El tamaño de :attribute debe ser :size kilobytes.',
        'string'  => ':attribute debe contener :size caracteres.',
        'array'   => ':attribute debe contener :size elementos.',
    ],
    'string'               => 'El :attribute debe ser una cadena de caracteres.',
    'timezone'             => 'El :attribute debe ser una zona válida.',
    'unique'               => 'El :attribute ya ha sido registrado.',
    'uploaded'             => 'El :attribute error al subir.',
    'url'                  => 'El formato :attribute es inválido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */
    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */
    'attributes'           => [
        'name' => 'nombre',
        'lastname' => 'lapellido',
        'firstname' => 'nombre',
        'social_security_number' => 'número de seguridad social',
        'curp' => 'curp',
        'email' => 'correo electrónico',
        'email2' => 'segundo correo electrónico',
        'email3' => 'tercer correo electrónico',
        'email4' => 'cuarto email',
        'date_of_birth' => 'fecha de nacimiento',
        'hour_of_birth' => 'hora de nacimiento',
        'country_id' => 'país',
        'state_id' => 'estado',
        'exterior_number' => 'numero exterior',
        'municipality_county' => 'municipio',
        'job_title' => 'título profesional',
        'name_es' => 'nombre',
        'name_en' => 'nombre',
        'brandsite_section_id' => 'sección del sitio de la marca',
        'country_code' => 'código de país',
        'events_calendar_category_id' => 'categoría de calendario de eventos',
        'start_datetime' => 'fecha de inicio',
        'end_datetime' => 'fecha final',
        'description' => 'descripción',
        'hotel_id' => 'hotel',
        'mahgazine_section_edition_id' => 'edición de sección de la revista',
        'revision_number' => 'número de revisión',
        'date' => 'fecha',
        'time' => 'hora',
        'score' => 'puntuación',
        'policy' => 'política',
        'application_reasoning' => 'razonamiento de aplicación',
        'guests_collateral_damage' => 'daños colaterales al huésped',
        'extension_date' => 'fecha de extensión',
        'assigned_user_id' => 'usuario asignado',
        'user_permissions_group_id' => 'grupo de permisos de usuario',
        'url' => 'url',
        'usd_currency' => 'moneda',
        'stock' => 'existencia',
        'quantity' => 'cantidad',
        'patient_id' => 'paciente',
        'account_number' => 'número de cuenta',
        'catalog_id' => 'catálogo',
        'code' => 'código',
        'short_code' => 'código corto',
        'external_company_id' => 'compañía externa',
        'amount' => 'cantidad',
        'street' => 'calle',
        'colony' => 'colonia',
        'zip' => 'código postal',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
        'template_slug' => 'template'
    ],
];