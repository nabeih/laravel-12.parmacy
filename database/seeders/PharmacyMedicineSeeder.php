<?php

namespace Database\Seeders;

use App\Models\Active_Ingredient;
use App\Models\Dosage_Form;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Medicine_Category;
use App\Models\Pharmacist;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Seeder;

class PharmacyMedicineSeeder extends Seeder
{
    /**
     * Seed two pharmacies and two medicines so the customer catalog/search has data to show.
     */
    public function run(): void
    {
        $manufacturer = Manufacturer::firstOrCreate(
            ['name_en' => 'PharmaLink Labs'],
            ['name_ar' => 'مختبرات فارمالينك', 'address' => 'عمّان، الأردن', 'phone' => '0600000000', 'is_active' => true]
        );

        $category = Medicine_Category::firstOrCreate(
            ['name_en' => 'Pain Relief'],
            ['name_ar' => 'مسكنات الألم', 'is_active' => true]
        );

        $dosageForm = Dosage_Form::firstOrCreate(
            ['name_en' => 'Tablet'],
            ['name_ar' => 'أقراص', 'is_active' => true]
        );

        $paracetamol = Active_Ingredient::firstOrCreate(
            ['name_en' => 'Paracetamol'],
            ['name_ar' => 'باراسيتامول', 'is_active' => true]
        );

        $ibuprofen = Active_Ingredient::firstOrCreate(
            ['name_en' => 'Ibuprofen'],
            ['name_ar' => 'إيبوبروفين', 'is_active' => true]
        );

        $pharmacistsData = [
            [
                'user' => [
                    'name' => 'صيدلاني تجريبي 1',
                    'email' => 'pharmacist1@pharmalink.com',
                ],
                'pharmacist' => [
                    'national_id' => 'SEED-NID-0001',
                    'syndicate_number' => 'SEED-SYN-0001',
                    'license_number' => 'SEED-LIC-0001',
                    'graduation_university' => 'الجامعة الأردنية',
                    'graduation_year' => 2015,
                ],
                'pharmacy' => [
                    'name_ar' => 'صيدلية الشفاء',
                    'name_en' => 'Al-Shifa Pharmacy',
                    'phone' => '0791111111',
                    'address' => 'شارع الجامعة، عمّان',
                ],
            ],
            [
                'user' => [
                    'name' => 'صيدلاني تجريبي 2',
                    'email' => 'pharmacist2@pharmalink.com',
                ],
                'pharmacist' => [
                    'national_id' => 'SEED-NID-0002',
                    'syndicate_number' => 'SEED-SYN-0002',
                    'license_number' => 'SEED-LIC-0002',
                    'graduation_university' => 'جامعة العلوم والتكنولوجيا',
                    'graduation_year' => 2018,
                ],
                'pharmacy' => [
                    'name_ar' => 'صيدلية النور',
                    'name_en' => 'Al-Noor Pharmacy',
                    'phone' => '0792222222',
                    'address' => 'شارع الملكة رانيا، عمّان',
                ],
            ],
        ];

        foreach ($pharmacistsData as $entry) {
            $user = User::updateOrCreate(
                ['email' => $entry['user']['email']],
                [
                    'name' => $entry['user']['name'],
                    'password' => 'password',
                    'role' => 'pharmacist',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $pharmacist = Pharmacist::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($entry['pharmacist'], [
                    'certificate_file' => 'seed/placeholder-certificate.pdf',
                    'syndicate_file' => 'seed/placeholder-syndicate.pdf',
                    'license_file' => 'seed/placeholder-license.pdf',
                    'status' => 'approved',
                    'approved_at' => now(),
                    'is_active' => true,
                ])
            );

            Pharmacy::updateOrCreate(
                ['pharmacist_id' => $pharmacist->id],
                array_merge($entry['pharmacy'], [
                    'opening_time' => '09:00',
                    'closing_time' => '22:00',
                    'status' => 'opne',
                    'is_verified' => true,
                ])
            );
        }

        $medicinesData = [
            [
                'brand_name_en' => 'Panadol',
                'brand_name_ar' => 'بانادول',
                'reference_price' => 1.50,
                'description_en' => 'Pain and fever relief tablets.',
                'description_ar' => 'أقراص لتسكين الألم وخفض الحرارة.',
                'requires_prescription' => false,
                'ingredient' => $paracetamol,
                'strength_value' => 500,
                'strength_unit' => 'mg',
            ],
            [
                'brand_name_en' => 'Advil',
                'brand_name_ar' => 'أدفيل',
                'reference_price' => 2.25,
                'description_en' => 'Anti-inflammatory pain relief tablets.',
                'description_ar' => 'أقراص مضادة للالتهاب ومسكنة للألم.',
                'requires_prescription' => false,
                'ingredient' => $ibuprofen,
                'strength_value' => 400,
                'strength_unit' => 'mg',
            ],
        ];

        foreach ($medicinesData as $data) {
            $ingredient = $data['ingredient'];
            $strengthValue = $data['strength_value'];
            $strengthUnit = $data['strength_unit'];
            unset($data['ingredient'], $data['strength_value'], $data['strength_unit']);

            $medicine = Medicine::updateOrCreate(
                ['brand_name_en' => $data['brand_name_en']],
                array_merge($data, [
                    'manufacturer_id' => $manufacturer->id,
                    'category_id' => $category->id,
                    'dosage_form_id' => $dosageForm->id,
                    'is_active' => true,
                ])
            );

            $medicine->activeIngredients()->syncWithoutDetaching([
                $ingredient->id => ['strength_value' => $strengthValue, 'strength_unit' => $strengthUnit],
            ]);
        }
    }
}
