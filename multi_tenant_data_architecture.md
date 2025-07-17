# Multi-Tenant Data Architecture Recommendations

## Current State
- **Single Database Multi-Tenancy** untuk core data (users, roles, settings)
- Menggunakan `tenant_id` column dengan `HasTenant` trait
- Global scopes untuk data isolation

## Recommended Architecture: Hybrid Multi-Tenancy

### 1. **Core Database** (Current - Keep as is)
```
orchestrator_main_db
├── tenants
├── users  
├── roles
├── permissions
├── system_settings
├── menus
├── tenant_api_credentials
└── api_usage_logs
```

### 2. **Separate Transaction Database** (New)
```
orchestrator_transactions_db
├── transactions_{tenant_id}
├── orders_{tenant_id}
├── products_{tenant_id}
├── inventory_{tenant_id}
├── invoices_{tenant_id}
└── payments_{tenant_id}
```

### 3. **Benefits of This Approach**

#### **Performance**
- Transaction queries tidak impact core system
- Bisa scale database transaction secara independent
- Indexing lebih optimal per tenant

#### **Security**
- Physical isolation untuk sensitive transaction data
- Tenant tidak bisa accidentally query data tenant lain
- Easier backup/restore per tenant

#### **Scalability**
- Horizontal scaling untuk high-volume tenants
- Bisa move specific tenant ke dedicated server
- Database sharding per tenant atau grup tenant

## Implementation Strategy

### Phase 1: Database Configuration
```php
// config/database.php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => env('DB_DATABASE', 'orchestrator_main'),
        // ... main database config
    ],
    
    'transactions' => [
        'driver' => 'mysql', 
        'host' => env('TRANSACTIONS_DB_HOST', '127.0.0.1'),
        'database' => env('TRANSACTIONS_DB_DATABASE', 'orchestrator_transactions'),
        'username' => env('TRANSACTIONS_DB_USERNAME', 'root'),
        'password' => env('TRANSACTIONS_DB_PASSWORD', ''),
        // ... transaction database config
    ],
]
```

### Phase 2: Transaction Models
```php
// app/Models/Transaction.php
class Transaction extends Model
{
    protected $connection = 'transactions';
    protected $table = 'transactions';
    
    use HasTenant; // Still use tenant_id for additional security
    
    protected $fillable = [
        'tenant_id',
        'transaction_id',
        'amount',
        'status',
        'created_at',
        'updated_at'
    ];
}
```

### Phase 3: Dynamic Table Names (Optional)
```php
// Untuk complete isolation
class Transaction extends Model
{
    protected $connection = 'transactions';
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $tenantId = app('current_tenant_id');
        $this->table = "transactions_{$tenantId}";
    }
}
```

### Phase 4: Service Layer
```php
// app/Services/TransactionService.php
class TransactionService
{
    public function createTransaction($tenantId, $data)
    {
        DB::connection('transactions')->transaction(function() use ($tenantId, $data) {
            // Create transaction with tenant isolation
            $transaction = new Transaction();
            $transaction->setTable("transactions_{$tenantId}");
            $transaction->fill($data);
            $transaction->tenant_id = $tenantId;
            $transaction->save();
            
            return $transaction;
        });
    }
}
```

## Migration Strategy

### Step 1: Create Transaction Database
```bash
# Create new database
CREATE DATABASE orchestrator_transactions;

# Run transaction-specific migrations
php artisan migrate --database=transactions --path=database/migrations/transactions
```

### Step 2: Data Migration
```php
// Migrate existing transaction data
php artisan make:command MigrateTransactionData

class MigrateTransactionData extends Command
{
    public function handle()
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $this->info("Migrating data for tenant: {$tenant->name}");
            
            // Create tenant-specific tables
            $this->createTenantTables($tenant->id);
            
            // Migrate data
            $this->migrateTransactionData($tenant->id);
        }
    }
}
```

### Step 3: API Integration
```php
// app/Http/Controllers/Api/TransactionController.php
class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $tenantId = app('current_tenant_id');
        
        $transaction = DB::connection('transactions')
            ->table("transactions_{$tenantId}")
            ->insert($request->validated());
            
        return response()->json($transaction);
    }
}
```

## Security Considerations

1. **Database Access Control**
   - Separate database credentials untuk transaction DB
   - Network isolation antara databases
   - Encrypted connections

2. **API Key Validation**
   - Validate API key against main database
   - Get tenant_id from API key
   - Use tenant_id untuk akses transaction database

3. **Backup Strategy**
   - Separate backup schedules
   - Per-tenant restore capability
   - Point-in-time recovery

## Alternative Architectures

### Option 1: Database per Tenant
```
tenant_1_db
├── transactions
├── orders
└── products

tenant_2_db
├── transactions
├── orders
└── products
```

**Pros**: Complete isolation, easier compliance
**Cons**: Management complexity, resource overhead

### Option 2: Schema per Tenant
```
orchestrator_transactions_db
├── tenant_1.transactions
├── tenant_1.orders
├── tenant_2.transactions
└── tenant_2.orders
```

**Pros**: Good isolation, manageable
**Cons**: Database-specific features

### Option 3: Microservices Architecture
```
orchestrator-core (main app)
├── tenant-service
├── user-service
└── api-gateway

orchestrator-transactions (separate service)
├── transaction-service
├── order-service
└── payment-service
```

## Recommendation

Untuk case Anda, saya rekomendasikan **Hybrid Multi-Tenancy** dengan:

1. **Keep current architecture** untuk core data
2. **Separate transaction database** dengan table per tenant
3. **Gradual migration** starting dengan high-volume tables
4. **Maintain API key isolation** yang sudah ada

Ini memberikan balance antara performance, security, dan complexity management.