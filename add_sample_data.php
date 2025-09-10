<?php
require_once 'includes/config.php';

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert sample races
    $stmt = $pdo->prepare("INSERT INTO races (name, description, location, event_date, whatsapp_group_link) VALUES (?, ?, ?, ?, ?)");
    
    $races = [
        [
            'name' => 'Balap Sepeda Anak Lampung 2023',
            'description' => 'A fun and exciting pushbike race for children of all ages',
            'location' => 'Lapangan Merdeka, Bandar Lampung',
            'event_date' => '2023-12-10',
            'whatsapp_group_link' => 'https://chat.whatsapp.com/sample1'
        ],
        [
            'name' => 'Kids Bike Festival 2023',
            'description' => 'Annual bike festival for kids with various categories',
            'location' => 'Taman Budaya, Bandar Lampung',
            'event_date' => '2023-11-25',
            'whatsapp_group_link' => 'https://chat.whatsapp.com/sample2'
        ],
        [
            'name' => 'Family Bike Day 2023',
            'description' => 'A family-friendly bike event with races and activities',
            'location' => 'Kartini Beach, Bandar Lampung',
            'event_date' => '2023-12-17',
            'whatsapp_group_link' => 'https://chat.whatsapp.com/sample3'
        ]
    ];
    
    foreach ($races as $race) {
        $stmt->execute([
            $race['name'],
            $race['description'],
            $race['location'],
            $race['event_date'],
            $race['whatsapp_group_link']
        ]);
        
        $raceId = $pdo->lastInsertId();
        
        // Insert categories for this race
        $categories = [
            ['name' => 'Kategori 3-5 Tahun', 'quota' => 30, 'age_group' => '3-5 years', 'registration_fee' => 50000],
            ['name' => 'Kategori 6-8 Tahun', 'quota' => 40, 'age_group' => '6-8 years', 'registration_fee' => 75000],
            ['name' => 'Kategori 9-12 Tahun', 'quota' => 50, 'age_group' => '9-12 years', 'registration_fee' => 100000]
        ];
        
        $catStmt = $pdo->prepare("INSERT INTO categories (race_id, name, quota, age_group, registration_fee) VALUES (?, ?, ?, ?, ?)");
        foreach ($categories as $category) {
            $catStmt->execute([$raceId, $category['name'], $category['quota'], $category['age_group'], $category['registration_fee']]);
        }
        
        // Insert form fields for this race
        $formFields = [
            ['field_name' => 'Nama Lengkap Peserta', 'field_type' => 'text', 'is_required' => 1],
            ['field_name' => 'Tanggal Lahir', 'field_type' => 'date', 'is_required' => 1],
            ['field_name' => 'Nama Orang Tua', 'field_type' => 'text', 'is_required' => 1],
            ['field_name' => 'Nomor Telepon Orang Tua', 'field_type' => 'text', 'is_required' => 1],
            ['field_name' => 'Bukti Transfer', 'field_type' => 'file', 'is_required' => 1]
        ];
        
        $formStmt = $pdo->prepare("INSERT INTO forms (race_id, field_name, field_type, is_required) VALUES (?, ?, ?, ?)");
        foreach ($formFields as $field) {
            $formStmt->execute([$raceId, $field['field_name'], $field['field_type'], $field['is_required']]);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo "Sample data added successfully!";
    echo "<br><a href='/user/races.php'>View Races</a>";
    
} catch(PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>