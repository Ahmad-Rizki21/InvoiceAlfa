<?php

return [
    /*
    |---------------------------------------------------------------------------------------
    | Baris Bahasa untuk Validasi
    |---------------------------------------------------------------------------------------
    |
    | Baris bahasa berikut ini berisi standar pesan kesalahan yang digunakan oleh
    | kelas validasi. Beberapa aturan mempunyai banyak versi seperti aturan 'size'.
    | Jangan ragu untuk mengoptimalkan setiap pesan yang ada di sini.
    |
    */

    'accepted'        => ':Attribute harus diterima.',
    'active_url'      => ':Attribute bukan URL yang valid.',
    'after'           => ':Attribute harus berisi tanggal setelah :date.',
    'after_or_equal'  => ':Attribute harus berisi tanggal setelah atau sama dengan :date.',
    'alpha'           => ':Attribute hanya boleh berisi huruf.',
    'alpha_dash'      => ':Attribute hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num'       => ':Attribute hanya boleh berisi huruf dan angka.',
    'array'           => ':Attribute harus berisi sebuah array.',
    'before'          => ':Attribute harus berisi tanggal sebelum :date.',
    'before_or_equal' => ':Attribute harus berisi tanggal sebelum atau sama dengan :date.',
    'between'         => [
        'numeric' => ':Attribute harus bernilai antara :min sampai :max.',
        'file'    => ':Attribute harus berukuran antara :min sampai :max kilobita.',
        'string'  => ':Attribute harus berisi antara :min sampai :max karakter.',
        'array'   => ':Attribute harus memiliki :min sampai :max anggota.',
    ],
    'boolean'        => ':Attribute harus bernilai true atau false',
    'confirmed'      => 'Konfirmasi :attribute tidak cocok.',
    'date'           => ':Attribute bukan tanggal yang valid.',
    'date_equals'    => ':Attribute harus berisi tanggal yang sama dengan :date.',
    'date_format'    => ':Attribute tidak cocok dengan format :format.',
    'different'      => ':Attribute dan :other harus berbeda.',
    'digits'         => ':Attribute harus terdiri dari :digits angka.',
    'digits_between' => ':Attribute harus terdiri dari :min sampai :max angka.',
    'dimensions'     => ':Attribute tidak memiliki dimensi gambar yang valid.',
    'distinct'       => ':Attribute memiliki nilai yang duplikat.',
    'email'          => ':Attribute harus berupa alamat surel yang valid.',
    'ends_with'      => ':Attribute harus diakhiri salah satu dari berikut: :values',
    'exists'         => ':Attribute yang dipilih tidak valid.',
    'file'           => ':Attribute harus berupa sebuah berkas.',
    'filled'         => ':Attribute harus memiliki nilai.',
    'gt'             => [
        'numeric' => ':Attribute harus bernilai lebih besar dari :value.',
        'file'    => ':Attribute harus berukuran lebih besar dari :value kilobita.',
        'string'  => ':Attribute harus berisi lebih besar dari :value karakter.',
        'array'   => ':Attribute harus memiliki lebih dari :value anggota.',
    ],
    'gte' => [
        'numeric' => ':Attribute harus bernilai lebih besar dari atau sama dengan :value.',
        'file'    => ':Attribute harus berukuran lebih besar dari atau sama dengan :value kilobita.',
        'string'  => ':Attribute harus berisi lebih besar dari atau sama dengan :value karakter.',
        'array'   => ':Attribute harus terdiri dari :value anggota atau lebih.',
    ],
    'image'    => ':Attribute harus berupa gambar.',
    'in'       => ':Attribute yang dipilih tidak valid.',
    'in_array' => ':Attribute tidak ada di dalam :other.',
    'integer'  => ':Attribute harus berupa bilangan bulat.',
    'ip'       => ':Attribute harus berupa alamat IP yang valid.',
    'ipv4'     => ':Attribute harus berupa alamat IPv4 yang valid.',
    'ipv6'     => ':Attribute harus berupa alamat IPv6 yang valid.',
    'json'     => ':Attribute harus berupa JSON string yang valid.',
    'lt'       => [
        'numeric' => ':Attribute harus bernilai kurang dari :value.',
        'file'    => ':Attribute harus berukuran kurang dari :value kilobita.',
        'string'  => ':Attribute harus berisi kurang dari :value karakter.',
        'array'   => ':Attribute harus memiliki kurang dari :value anggota.',
    ],
    'lte' => [
        'numeric' => ':Attribute harus bernilai kurang dari atau sama dengan :value.',
        'file'    => ':Attribute harus berukuran kurang dari atau sama dengan :value kilobita.',
        'string'  => ':Attribute harus berisi kurang dari atau sama dengan :value karakter.',
        'array'   => ':Attribute harus tidak lebih dari :value anggota.',
    ],
    'max' => [
        'numeric' => ':Attribute maskimal bernilai :max.',
        'file'    => ':Attribute maksimal berukuran :max kilobita.',
        'string'  => ':Attribute maskimal berisi :max karakter.',
        'array'   => ':Attribute maksimal terdiri dari :max anggota.',
    ],
    'mimes'     => ':Attribute harus berupa berkas berjenis: :values.',
    'mimetypes' => ':Attribute harus berupa berkas berjenis: :values.',
    'min'       => [
        'numeric' => ':Attribute minimal bernilai :min.',
        'file'    => ':Attribute minimal berukuran :min kilobita.',
        'string'  => ':Attribute minimal berisi :min karakter.',
        'array'   => ':Attribute minimal terdiri dari :min anggota.',
    ],
    'not_in'               => ':Attribute yang dipilih tidak valid.',
    'not_regex'            => 'Format :attribute tidak valid.',
    'numeric'              => ':Attribute harus berupa angka.',
    'password'             => 'Kata sandi salah.',
    'present'              => ':Attribute wajib ada.',
    'regex'                => 'Format :attribute tidak valid.',
    'required'             => ':Attribute wajib diisi.',
    'required_if'          => ':Attribute wajib diisi bila :other adalah :value.',
    'required_unless'      => ':Attribute wajib diisi kecuali :other memiliki nilai :values.',
    'required_with'        => ':Attribute wajib diisi bila terdapat :values.',
    'required_with_all'    => ':Attribute wajib diisi bila terdapat :values.',
    'required_without'     => ':Attribute wajib diisi bila tidak terdapat :values.',
    'required_without_all' => ':Attribute wajib diisi bila sama sekali tidak terdapat :values.',
    'same'                 => ':Attribute dan :other harus sama.',
    'size'                 => [
        'numeric' => ':Attribute harus berukuran :size.',
        'file'    => ':Attribute harus berukuran :size kilobyte.',
        'string'  => ':Attribute harus berukuran :size karakter.',
        'array'   => ':Attribute harus mengandung :size anggota.',
    ],
    'starts_with' => ':Attribute harus diawali salah satu dari berikut: :values',
    'string'      => ':Attribute harus berupa string.',
    'timezone'    => ':Attribute harus berisi zona waktu yang valid.',
    'unique'      => ':Attribute sudah ada sebelumnya.',
    'uploaded'    => ':Attribute gagal diunggah.',
    'url'         => 'Format :attribute tidak valid.',
    'uuid'        => ':Attribute harus merupakan UUID yang valid.',

    /*
    |---------------------------------------------------------------------------------------
    | Baris Bahasa untuk Validasi Kustom
    |---------------------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan pesan validasi untuk atribut sesuai keinginan dengan
    | menggunakan konvensi "attribute.rule" dalam penamaan barisnya. Hal ini mempercepat
    | dalam menentukan baris bahasa kustom yang spesifik untuk aturan atribut yang diberikan.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |---------------------------------------------------------------------------------------
    | Kustom Validasi Atribut
    |---------------------------------------------------------------------------------------
    |
    | Baris bahasa berikut digunakan untuk menukar 'placeholder' atribut dengan sesuatu
    | yang lebih mudah dimengerti oleh pembaca seperti "Alamat Surel" daripada "surel" saja.
    | Hal ini membantu kita dalam membuat pesan menjadi lebih ekspresif.
    |
    */

    'attributes' => [
        'started_at' => 'Jam mulai',
        'ended_at' => 'Jam selesai',
        'date' => 'Tanggal',
        'name' => 'Nama',
        'topic' => 'Nama kegiatan',
        'total_participant' => 'Jumlah peserta',
        'candidate_profile_id' => 'Calon',
        'candidate_id' => 'Calon',
        'province_code' => 'Provinsi',
        'city_code' => 'Kota/Kabupaten',
        'subdistrict_code' => 'Kecamatan',
        'village_code' => 'Kelurahan/Desa',
        'hamlet' => 'RW',
        'neighbourhood' => 'RT',

        'gender_id' => 'Jenis kelamin',
        'religion_id' => 'Agama',
        'blood_group_id' => 'Golongan darah',
        'marital_status_id' => 'Status perkawinan',
        'job_classification_id' => 'Jenis pekerjaan',

        'vote_target' => 'Target suara',
        'quick_count_result' => 'Hasil Quick Count',
        'real_count' => 'Real count',
        'total_voter' => 'Jumlah pemilih',
        'nik' => 'NIK',
        'created_by_id' => 'Perekrut',

        'buttons.*.type' => 'Tipe',
        'buttons.*.link' => 'Link',
        'buttons.*.display' => 'Display',
        'buttons.*.phone' => 'Phone',
        'buttons.*.quickReply' => 'Quick Reply',

        'branch_id' => 'Lokasi',

        'customer_id' => 'klien',
        'user_id' => 'pengguna',
        'ticket_id' => 'tiket',
        'role_id' => 'jabatan',
        'remote_location_id' => 'lokasi remote',
        'postal_code' => 'kode pos',
        'pic_remote_name' => 'nama PIC remote',
        'pic_remote_phone_number' => 'no. ponsel PIC remote',
        'pic_it_sat_name' => 'nama PIC IT SAT',
        'pic_it_sat_phone_number' => 'no. ponsel PIC IT SAT',
        'infrastructure_type' => 'tipe infrastruktur',
        'gsm_no' => 'no. GSM',
        'gsm_provider' => 'provider GSM',
        'gsm_no2' => 'no. GSM 2',
        'gsm_provider2' => 'provider GSM 2',
        'cid_provider' => 'CID provider',
        'fo_provider' => 'FO provider',
        'pic_fo_provider_name' => 'nama PIC FO provider',
        'pic_fo_provider_phone_number' => 'no. ponsel PIC FO provider',
        'fo_contract_by_name' => 'atas nama FO kontrak',
        'created_at' => 'dibuat pada',
        'updated_at' => 'diperbarui pada',
        'progress_message' => 'status progres',
        'down_time_caused_by' => 'penyebab down time',
        'total_tickets' => 'total tiket',
        'progress_message' => 'keterangan',
    ],
];
