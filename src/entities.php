<?php

declare(strict_types=1);

/*
 * Trusted metadata used by the reusable CRUD controller. SQL identifiers are
 * never taken from request data; requests may only select a key in this map.
 */
const ENTITY_CONFIG = [
    'suppliers' => [
        'title' => 'Suppliers', 'table' => 'supplier', 'pk' => 'supplier_id',
        'permission' => 'manage_inventory', 'module' => 'inventory',
        'search' => ['supplier_code', 'supplier_name', 'estate_name', 'district'],
        'fields' => [
            'supplier_code' => ['label' => 'Supplier Code', 'required' => true],
            'supplier_name' => ['label' => 'Supplier Name', 'required' => true],
            'estate_name' => ['label' => 'Estate Name', 'required' => true],
            'contact_person' => ['label' => 'Contact Person'],
            'phone' => ['label' => 'Phone'],
            'email' => ['label' => 'Email', 'type' => 'email'],
            'address' => ['label' => 'Address', 'type' => 'textarea'],
            'district' => ['label' => 'District'],
            'supplier_status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Active', 'Inactive']],
        ],
    ],
    'tea-leaves' => [
        'title' => 'Tea Leaf Inventory', 'table' => 'tea_leaf_inventory', 'pk' => 'tea_leaf_id',
        'permission' => 'manage_inventory', 'module' => 'inventory',
        'search' => ['tea_leaf_code', 'leaf_type', 'quality_grade', 'storage_location'],
        'fields' => [
            'tea_leaf_code' => ['label' => 'Leaf Code', 'required' => true],
            'supplier_id' => ['label' => 'Supplier', 'type' => 'foreign', 'required' => true, 'table' => 'supplier', 'pk' => 'supplier_id', 'display' => 'supplier_name'],
            'leaf_type' => ['label' => 'Leaf Type', 'required' => true],
            'quality_grade' => ['label' => 'Quality Grade'],
            'received_quantity' => ['label' => 'Received Quantity', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'current_stock' => ['label' => 'Current Stock', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'unit' => ['label' => 'Unit', 'type' => 'select', 'options' => ['kg', 'g', 'ton']],
            'received_date' => ['label' => 'Received Date', 'type' => 'date', 'required' => true],
            'storage_location' => ['label' => 'Storage Location'],
        ],
    ],
    'materials' => [
        'title' => 'Other Materials', 'table' => 'other_material', 'pk' => 'material_id',
        'permission' => 'manage_inventory', 'module' => 'inventory',
        'search' => ['material_code', 'material_name', 'category', 'storage_location'],
        'fields' => [
            'material_code' => ['label' => 'Material Code', 'required' => true],
            'material_name' => ['label' => 'Material Name', 'required' => true],
            'category' => ['label' => 'Category', 'type' => 'select', 'options' => ['Packaging', 'Maintenance', 'Fuel', 'Cleaning', 'Utility', 'Other']],
            'unit' => ['label' => 'Unit', 'required' => true],
            'current_stock' => ['label' => 'Current Stock', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'minimum_stock' => ['label' => 'Minimum Stock', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'storage_location' => ['label' => 'Storage Location'],
        ],
    ],
    'products' => [
        'title' => 'Tea Products', 'table' => 'tea_product', 'pk' => 'product_id',
        'permission' => 'manage_inventory', 'module' => 'inventory',
        'search' => ['product_code', 'product_name', 'tea_grade'],
        'fields' => [
            'product_code' => ['label' => 'Product Code', 'required' => true],
            'product_name' => ['label' => 'Product Name', 'required' => true],
            'tea_grade' => ['label' => 'Tea Grade', 'required' => true],
            'package_weight' => ['label' => 'Package Weight', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'package_unit' => ['label' => 'Package Unit', 'type' => 'select', 'options' => ['g', 'kg']],
            'selling_price' => ['label' => 'Selling Price', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'description' => ['label' => 'Description', 'type' => 'textarea'],
            'product_status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Active', 'Inactive']],
        ],
    ],
    'batches' => [
        'title' => 'Production Batches', 'table' => 'production_batch', 'pk' => 'batch_id',
        'permission' => 'manage_production', 'module' => 'production',
        'search' => ['batch_code', 'batch_status', 'remarks'],
        'fields' => [
            'batch_code' => ['label' => 'Batch Code', 'required' => true],
            'created_by' => ['label' => 'Created By', 'type' => 'current_user', 'required' => true],
            'production_date' => ['label' => 'Production Date', 'type' => 'date', 'required' => true],
            'total_leaf_weight' => ['label' => 'Leaf Weight (kg)', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'expected_output' => ['label' => 'Expected Output (kg)', 'type' => 'number', 'step' => '0.01'],
            'batch_status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Planned', 'In Progress', 'Completed', 'Cancelled']],
            'remarks' => ['label' => 'Remarks', 'type' => 'textarea'],
        ],
    ],
    'stages' => [
        'title' => 'Production Stages', 'table' => 'production_stage', 'pk' => 'stage_id',
        'permission' => 'manage_production', 'module' => 'production',
        'search' => ['stage_code', 'stage_name', 'stage_status'],
        'fields' => [
            'stage_code' => ['label' => 'Stage Code', 'required' => true],
            'stage_name' => ['label' => 'Stage Name', 'required' => true],
            'sequence_number' => ['label' => 'Sequence Number', 'type' => 'number', 'required' => true],
            'standard_duration_minutes' => ['label' => 'Standard Duration (minutes)', 'type' => 'number'],
            'description' => ['label' => 'Description', 'type' => 'textarea'],
            'stage_status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Active', 'Inactive']],
        ],
    ],
    'processes' => [
        'title' => 'Production Processes', 'table' => 'production_process', 'pk' => 'process_id',
        'permission' => 'manage_production', 'module' => 'production',
        'search' => ['process_status', 'remarks'],
        'fields' => [
            'batch_id' => ['label' => 'Batch', 'type' => 'foreign', 'required' => true, 'table' => 'production_batch', 'pk' => 'batch_id', 'display' => 'batch_code'],
            'stage_id' => ['label' => 'Stage', 'type' => 'foreign', 'required' => true, 'table' => 'production_stage', 'pk' => 'stage_id', 'display' => 'stage_name'],
            'machine_id' => ['label' => 'Machine', 'type' => 'foreign', 'required' => true, 'table' => 'machine', 'pk' => 'machine_id', 'display' => 'machine_name'],
            'start_time' => ['label' => 'Start Time', 'type' => 'datetime-local'],
            'end_time' => ['label' => 'End Time', 'type' => 'datetime-local'],
            'duration_minutes' => ['label' => 'Duration (minutes)', 'type' => 'number'],
            'input_quantity' => ['label' => 'Input Quantity', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'output_quantity' => ['label' => 'Output Quantity', 'type' => 'number', 'step' => '0.01'],
            'waste_quantity' => ['label' => 'Waste Quantity', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'process_status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Pending', 'Running', 'Completed', 'Interrupted']],
            'remarks' => ['label' => 'Remarks', 'type' => 'textarea'],
        ],
    ],
    'machines' => [
        'title' => 'Machines', 'table' => 'machine', 'pk' => 'machine_id',
        'permission' => 'manage_machines', 'module' => 'machines',
        'search' => ['machine_code', 'machine_name', 'machine_type', 'location'],
        'fields' => [
            'machine_code' => ['label' => 'Machine Code', 'required' => true],
            'machine_name' => ['label' => 'Machine Name', 'required' => true],
            'machine_type' => ['label' => 'Machine Type', 'required' => true],
            'manufacturer' => ['label' => 'Manufacturer'],
            'model_number' => ['label' => 'Model Number'],
            'installation_date' => ['label' => 'Installation Date', 'type' => 'date'],
            'location' => ['label' => 'Location'],
            'machine_status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Running', 'Idle', 'Maintenance', 'Out of Service']],
        ],
    ],
    'sensors' => [
        'title' => 'Sensors', 'table' => 'sensor', 'pk' => 'sensor_id',
        'permission' => 'manage_machines', 'module' => 'machines',
        'search' => ['sensor_code', 'sensor_name', 'sensor_type', 'sensor_status'],
        'fields' => [
            'sensor_code' => ['label' => 'Sensor Code', 'required' => true],
            'machine_id' => ['label' => 'Machine', 'type' => 'foreign', 'required' => true, 'table' => 'machine', 'pk' => 'machine_id', 'display' => 'machine_name'],
            'sensor_name' => ['label' => 'Sensor Name', 'required' => true],
            'sensor_type' => ['label' => 'Sensor Type', 'type' => 'select', 'options' => ['Temperature', 'Humidity', 'Speed', 'Vibration']],
            'unit' => ['label' => 'Unit', 'required' => true],
            'installation_date' => ['label' => 'Installation Date', 'type' => 'date'],
            'sensor_status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Active', 'Inactive', 'Faulty']],
        ],
    ],
    'maintenance' => [
        'title' => 'Maintenance', 'table' => 'maintenance', 'pk' => 'maintenance_id',
        'permission' => 'manage_maintenance', 'module' => 'maintenance',
        'search' => ['maintenance_type', 'maintenance_status', 'description'],
        'fields' => [
            'machine_id' => ['label' => 'Machine', 'type' => 'foreign', 'required' => true, 'table' => 'machine', 'pk' => 'machine_id', 'display' => 'machine_name'],
            'performed_by' => ['label' => 'Performed By', 'type' => 'foreign', 'required' => true, 'table' => 'users', 'pk' => 'user_id', 'display' => 'full_name'],
            'maintenance_type' => ['label' => 'Type', 'type' => 'select', 'options' => ['Preventive', 'Corrective', 'Emergency']],
            'maintenance_date' => ['label' => 'Date', 'type' => 'date', 'required' => true],
            'description' => ['label' => 'Description', 'type' => 'textarea'],
            'maintenance_status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Scheduled', 'Completed', 'Cancelled']],
            'next_maintenance_date' => ['label' => 'Next Maintenance Date', 'type' => 'date'],
        ],
    ],
    'quality' => [
        'title' => 'Quality Inspections', 'table' => 'quality_control', 'pk' => 'quality_id',
        'permission' => 'manage_quality', 'module' => 'quality',
        'search' => ['quality_status', 'colour', 'aroma', 'appearance'],
        'fields' => [
            'batch_id' => ['label' => 'Batch', 'type' => 'foreign', 'required' => true, 'table' => 'production_batch', 'pk' => 'batch_id', 'display' => 'batch_code'],
            'inspected_by' => ['label' => 'Inspected By', 'type' => 'current_user', 'required' => true],
            'inspection_date' => ['label' => 'Inspection Date', 'type' => 'date', 'required' => true],
            'moisture_content' => ['label' => 'Moisture Content (%)', 'type' => 'number', 'step' => '0.01'],
            'colour' => ['label' => 'Colour'],
            'aroma' => ['label' => 'Aroma'],
            'appearance' => ['label' => 'Appearance'],
            'quality_status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Passed', 'Failed', 'Pending']],
            'remarks' => ['label' => 'Remarks', 'type' => 'textarea'],
        ],
    ],
    'finished-products' => [
        'title' => 'Finished Product Inventory', 'table' => 'finished_product', 'pk' => 'finished_product_id',
        'permission' => 'manage_inventory', 'module' => 'inventory',
        'search' => ['warehouse_location', 'stock_status'],
        'fields' => [
            'product_id' => ['label' => 'Product', 'type' => 'foreign', 'required' => true, 'table' => 'tea_product', 'pk' => 'product_id', 'display' => 'product_name'],
            'batch_id' => ['label' => 'Batch', 'type' => 'foreign', 'required' => true, 'table' => 'production_batch', 'pk' => 'batch_id', 'display' => 'batch_code'],
            'quantity' => ['label' => 'Quantity', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'manufacture_date' => ['label' => 'Manufacture Date', 'type' => 'date', 'required' => true],
            'expiry_date' => ['label' => 'Expiry Date', 'type' => 'date'],
            'warehouse_location' => ['label' => 'Warehouse Location'],
            'stock_status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Available', 'Reserved', 'Sold']],
        ],
    ],
];

function entityConfig(string $key): array
{
    if (!isset(ENTITY_CONFIG[$key])) {
        http_response_code(404);
        exit('Management section not found.');
    }

    return ENTITY_CONFIG[$key];
}
