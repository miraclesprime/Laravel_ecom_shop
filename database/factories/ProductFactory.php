<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Food product names (20 unique items)
        $names = [
            'Apples',
            'Oranges',
            'Milk',
            'Eggs',
            'Cheddar Cheese',
            'Butter',
            'Greek Yogurt',
            'Bread Loaf',
            'Pasta',
            'White Rice',
            'Tomatoes',
            'Potatoes',
            'Onions',
            'Carrots',
            'Broccoli',
            'Lettuce',
            'Chicken Breast',
            'Ground Beef',
            'Strawberries',
        ];

        // Ensure we pick unique names until the list is exhausted
        static $namePool = [];
        if (empty($namePool)) {
            $namePool = $names;
            shuffle($namePool);
        }
        $name = array_pop($namePool) ?? $this->faker->unique()->randomElement($names);

        // Realistic price map (USD)
        $priceMap = [
            'Apples' => 2.99,
            'Oranges' => 3.49,
            'Milk' => 2.49,
            'Eggs' => 3.99,
            'Cheddar Cheese' => 4.79,
            'Butter' => 3.49,
            'Greek Yogurt' => 5.99,
            'Bread Loaf' => 2.99,
            'Pasta' => 1.99,
            'White Rice' => 3.49,
            'Tomatoes' => 2.49,
            'Potatoes' => 3.99,
            'Onions' => 1.99,
            'Carrots' => 1.49,
            'Broccoli' => 2.29,
            'Lettuce' => 1.99,
            'Chicken Breast' => 7.99,
            'Ground Beef' => 6.49,
            'Strawberries' => 4.99,
        ];

        $price = $priceMap[$name] ?? $this->faker->randomFloat(2, 1.49, 9.99);

        // Map product names to bundled demo images in public/assets/product/prod_images
        $imageMap = [
            'Apples' => 'assets/product/prod_images/apples.jpg',
            'Oranges' => 'assets/product/prod_images/oranges.jpg',
            'Milk' => 'assets/product/prod_images/milk.jpg',
            'Eggs' => 'assets/product/prod_images/eggs.jpg',
            'Cheddar Cheese' => 'assets/product/prod_images/cheddar-cheese.jpg',
            'Butter' => 'assets/product/prod_images/butter.jpg',
            'Greek Yogurt' => 'assets/product/prod_images/greek-yogurt.jpg',
            'Bread Loaf' => 'assets/product/prod_images/bread-loaf.jpg',
            'Pasta' => 'assets/product/prod_images/pasta.jpg',
            'White Rice' => 'assets/product/prod_images/white-rice.jpg',
            'Tomatoes' => 'assets/product/prod_images/tomatoes.jpg',
            'Potatoes' => 'assets/product/prod_images/potatoes.jpg',
            'Onions' => 'assets/product/prod_images/onions.jpg',
            'Carrots' => 'assets/product/prod_images/carrots.jpg',
            'Broccoli' => 'assets/product/prod_images/broccoli.jpg',
            'Lettuce' => 'assets/product/prod_images/lettuce.jpg',
            'Chicken Breast' => 'assets/product/prod_images/chicken-breast.jpg',
            'Ground Beef' => 'assets/product/prod_images/ground-beef.jpg',
            'Strawberries' => 'assets/product/prod_images/strawberries.jpg',
        ];

        $image = $imageMap[$name] ?? 'assets/product/prod_images/placeholder.svg';

        return [
            'name' => $name,
            'image_url' => $image,
            'price' => $price,
            'stock_quantity' => $this->faker->numberBetween(8, 60),
            'low_stock_threshold' => $this->faker->numberBetween(3, 10),
        ];
    }
}
