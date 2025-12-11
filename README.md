# WooEasyLife - Advanced WooCommerce Management Plugin

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue)](https://wordpress.org)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://php.net)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.x-blue)](https://typescriptlang.org)

WooEasyLife is a comprehensive WordPress plugin that supercharges your WooCommerce store with advanced order management, fraud detection, courier integration, and customer analytics capabilities.

## 🚀 Features

### 📦 Order Management
- **Advanced Order Filtering**: Filter orders by status, customer, payment method, and more
- **Bulk Operations**: Process multiple orders simultaneously
- **Custom Order Statuses**: Create and manage custom order statuses
- **Order Notes & History**: Track order changes and add custom notes
- **Quick Status Changes**: One-click status updates with customizable buttons
- **Custom Checkout Fields Support**: Automatically captures and displays custom billing/shipping fields from checkout
- **Flexible Note Management**: Save custom field values to courier, invoice, or customer notes with dropdown selection

### 🔒 Fraud Detection & Security
- **Real-time Fraud Analysis**: AI-powered fraud detection system
- **Customer Risk Scoring**: Analyze customer behavior patterns
- **Delivery Success Prediction**: Predict order delivery probability
- **Blacklist Management**: Block suspicious IPs, phones, emails, and devices
- **Repeat Customer Detection**: Identify returning customers automatically

### 🚚 Courier Integration
- **Multi-Courier Support**: Integration with Steadfast and other courier services
- **Bulk Courier Entry**: Submit orders to couriers in batches
- **Real-time Status Sync**: Automatic status updates from courier APIs
- **Shipping Cost Calculation**: Dynamic shipping cost management
- **Delivery Tracking**: Track shipments directly from the dashboard
- **Inline Consignment Editing**: Double-click to edit consignment IDs with keyboard shortcuts support

### 📊 Analytics & Reporting
- **Sales Dashboard**: Comprehensive sales analytics and charts
- **Customer Analytics**: Deep customer behavior insights
- **Order Statistics**: Detailed order performance metrics
- **Fraud Reports**: Security and fraud analysis reports
- **Performance Metrics**: Track key business indicators

### 📱 Communication Tools
- **SMS Integration**: Automated SMS notifications for customers and admins
- **WhatsApp Integration**: Direct WhatsApp messaging from order interface
- **Email Notifications**: Customizable email templates
- **Customer Communication History**: Track all customer interactions

### 🛠️ Advanced Features
- **Missing Order Recovery**: Identify and recover abandoned orders
- **OTP Verification**: Phone number verification for order placement
- **Device Tracking**: Track customer devices for security
- **Custom Product Management**: Advanced product handling capabilities
- **Balance Management**: Built-in credit system for premium features
- **Cache Prevention**: Bypass all major caching plugins for real-time data
- **CORS Support**: Full cross-origin resource sharing for frontend integrations
- **Multi-Submenu Navigation**: Organized dashboard, orders, missing orders, and blacklist sections
- **Custom Field Management**: Full support for custom billing and shipping fields added by plugins or themes
- **Smart Field Detection**: Automatically identifies and separates custom fields from default WooCommerce fields
- **Interactive Field Display**: Hover-over tooltips showing custom field data in order lists
- **Multi-Destination Save**: Save custom field values to courier notes, invoice notes, or customer notes

## 🔧 Installation

### Requirements
- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

### Installation Steps

1. **Download the Plugin**
   ```bash
   git clone https://github.com/your-repo/woo-easy-life.git
   cd woo-easy-life
   ```

2. **Install Dependencies**
   ```bash
   # PHP Dependencies
   composer install
   
   # Frontend Dependencies
   cd vue-project
   npm install
   ```

3. **Build Frontend Assets**
   ```bash
   # Development build
   npm run dev
   
   # Production build
   npm run build
   ```

4. **Upload to WordPress**
   - Upload the plugin folder to `/wp-content/plugins/`
   - Activate the plugin through WordPress admin

5. **Configure the Plugin**
   - Navigate to WooEasyLife settings
   - Configure your API keys and preferences
   - Set up courier integrations

## ⚙️ Configuration

### Database Setup
The plugin automatically creates necessary database tables:
- Customer data tracking
- Fraud analysis records
- SMS history
- Block lists
- Custom order statuses

### API Configuration
Configure external service integrations:

```php
// Courier API Configuration
define('STEADFAST_API_KEY', 'your-api-key');
define('STEADFAST_SECRET_KEY', 'your-secret-key');

// SMS Configuration
define('SMS_API_ENDPOINT', 'your-sms-provider-endpoint');
define('SMS_API_KEY', 'your-sms-api-key');
```

### License Management
The plugin includes a built-in licensing system:
1. Purchase a license from the official website
2. Enter your license key in the plugin settings
3. Activate to unlock premium features

## 🎯 Usage

### Order Management
1. **Navigate to Orders**: Access the enhanced order interface
2. **Filter Orders**: Use advanced filters to find specific orders
3. **Bulk Actions**: Select multiple orders for batch processing
4. **Status Changes**: Use quick action buttons for status updates

### Fraud Detection
1. **Enable Fraud Check**: Turn on real-time fraud analysis
2. **Review Risk Scores**: Check customer risk indicators
3. **Manage Blacklists**: Add suspicious customers to block lists
4. **Monitor Reports**: Review fraud detection reports

### Courier Integration
1. **Configure Couriers**: Set up your courier service credentials
2. **Bulk Submit**: Send orders to couriers in batches
3. **Track Status**: Monitor delivery status automatically
4. **Handle Returns**: Process returned orders efficiently
5. **Edit Consignment ID**: Double-click the consignment ID to edit inline
   - Press **Enter** or click outside to save
   - Press **Escape** to cancel editing
   - Hover over the field to see the edit indicator

### Custom Checkout Fields
1. **Automatic Detection**: Custom fields are automatically detected and captured from checkout
2. **View in Admin**: Custom fields display in WordPress admin order details page with styled sections
3. **Order List Display**: Hover over info icon in order list to see custom field values
4. **Save to Notes**: Click the pencil icon and select where to save the field value:
   - **Courier Note**: For shipping/delivery instructions
   - **Invoice Note**: For billing or invoice-related information
   - **Customer Note**: For customer-specific details
5. **API Access**: Custom fields available via REST API in `billing_address.custom_fields` and `shipping_address.custom_fields`
6. **Compatible With**: CartFlows, WooCommerce Checkout Manager, Checkout Field Editor, and other custom field plugins

## 🔌 API Endpoints

### Order Management
```
GET    /wp-json/wooeasylife/v1/orders
POST   /wp-json/wooeasylife/v1/orders/change-status
GET    /wp-json/wooeasylife/v1/status-with-counts
POST   /wp-json/wooeasylife/v1/save-order-notes
```

**Order Response Structure** (including custom fields):
```json
{
  "billing_address": {
    "first_name": "John",
    "phone": "0123456789",
    "custom_fields": [
      {
        "key": "billing_special_instruction",
        "label": "Special Instruction",
        "value": "Please call before delivery"
      }
    ]
  },
  "shipping_address": {
    "first_name": "John",
    "custom_fields": [
      {
        "key": "shipping_preferred_time",
        "label": "Preferred Time",
        "value": "Morning delivery"
      }
    ]
  }
}
```

### Fraud Detection
```
POST   /wp-json/wooeasylife/v1/check-fraud-customer
GET    /wp-json/wooeasylife/v1/customer-data
POST   /wp-json/wooeasylife/v1/block-entry
```

### Courier Services
```
POST   /wp-json/wooeasylife/v1/courier/submit
GET    /wp-json/wooeasylife/v1/courier/status
PUT    /wp-json/wooeasylife/v1/courier/update
```

## 🎨 Frontend Architecture

### Technology Stack
- **Vue.js 3**: Progressive JavaScript framework
- **TypeScript**: Type-safe development
- **Tailwind CSS**: Utility-first CSS framework
- **Vite**: Fast build tool and development server

### Project Structure
```
vue-project/
├── src/
│   ├── components/        # Reusable UI components
│   ├── pages/            # Page components
│   ├── api/              # API integration layer
│   ├── helpers/          # Utility functions
│   └── types/            # TypeScript type definitions
├── public/               # Static assets
└── dist/                 # Build output
```

### Development Commands
```bash
# Start development server
npm run dev

# Build for production
npm run build

# Type checking
npm run type-check

# Watch mode for development
npm run watch
```

## 🧪 Testing

### PHP Testing
```bash
# Run PHPUnit tests
composer test

# Code quality checks
composer phpcs
composer phpstan
```

### Frontend Testing
```bash
# Run Vue component tests
npm run test

# Run E2E tests
npm run test:e2e
```

## 🚀 Deployment

### Production Build
```bash
# Build optimized assets
npm run build

# Optimize autoloader
composer install --no-dev --optimize-autoloader
```

### Performance Optimization
- Enable object caching (Redis/Memcached)
- Configure CDN for static assets
- Optimize database queries
- Use production API endpoints

## 🤝 Contributing

We welcome contributions! Please follow these guidelines:

1. **Fork the Repository**
2. **Create Feature Branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Commit Changes**
   ```bash
   git commit -m 'Add amazing feature'
   ```
4. **Push to Branch**
   ```bash
   git push origin feature/amazing-feature
   ```
5. **Open Pull Request**

### Coding Standards
- Follow WordPress PHP coding standards
- Use TypeScript for all frontend code
- Include comprehensive tests
- Document all public functions

## 📝 Changelog

### Version 1.1.9 (Latest)
- � FIX: Fixed issue where new orders were getting refreshed when clicking on courier refresh to bulk refresh

### Version 1.1.8
- 🚀 NEW: Double-click editable consignment ID field in order delivery partner section for quick inline editing
- 🚀 NEW: Keyboard shortcuts for consignment ID editing - Enter to save, Escape to cancel, click outside to save
- 🚀 NEW: Visual hover effects on editable consignment ID field with subtle border and background changes
- ⚡ IMPROVED: Enhanced UX for courier data management with instant inline updates without page refresh
- ⚡ IMPROVED: Consignment ID field now displays 'Not set' when empty for better user clarity
- ⚡ IMPROVED: Auto-focus on input field when entering edit mode for faster data entry
- ✨ ENHANCEMENT: Seamless editing experience with automatic save on blur (click outside)
- ✨ ENHANCEMENT: Better visual indication of editable fields with cursor pointer and tooltip
- ✨ ENHANCEMENT: Smoother transition between view and edit modes for consignment IDs

### Version 1.1.7
- 🚀 NEW: Dashboard submenu structure (Dashboard, Orders, Missing Orders, Black List) for better navigation
- 🚀 NEW: Comprehensive cache prevention system - prevents caching by all major WordPress cache plugins
- 🚀 NEW: Full CORS support for cross-origin API requests - enables seamless frontend integration
- 🚀 NEW: Protected against 12+ caching plugins including WP Rocket, LiteSpeed, Cloudflare, W3 Total Cache, and more
- 🚀 NEW: Custom checkout fields support - automatically captures billing and shipping custom fields
- 🚀 NEW: Smart custom field detection - separates custom fields from default WooCommerce fields
- 🚀 NEW: Custom fields display in WordPress admin order details page
- 🚀 NEW: Interactive custom field tooltips in order management interface
- 🚀 NEW: Dropdown menu to save custom field values to courier, invoice, or customer notes
- 🚀 NEW: Helper functions `get_billing_custom_fields()` and `get_shipping_custom_fields()`
- ⚡ IMPROVED: API responses now include comprehensive no-cache headers for real-time data
- ⚡ IMPROVED: Enhanced permission management - Shop Managers can now access WooEasyLife features
- ⚡ IMPROVED: Admin menu structure with organized submenus for better UX
- ⚡ IMPROVED: API endpoints now bypass all CDN/proxy caching layers
- ⚡ IMPROVED: Order API response now includes `custom_fields` array in billing and shipping addresses
- ⚡ IMPROVED: Custom field values can be appended to existing notes or create new ones
- ⚡ IMPROVED: Better handling of custom fields from CartFlows, WooCommerce Checkout Manager, and other plugins
- 🛡️ SECURITY: Added CORS headers with proper origin validation
- 🛡️ SECURITY: Implemented cache-busting headers to prevent sensitive data caching
- 🐛 FIX: Resolved API response caching issues across all major cache plugins
- 🐛 FIX: Fixed CORS policy errors for localhost and development environments
- 🐛 FIX: Custom field data properly structured with key, label, and value
- 🐛 FIX: Toast notifications for successful custom field saves
- ✨ ENHANCEMENT: Unique timestamp headers (X-WEL-Timestamp) for each API response
- ✨ ENHANCEMENT: Better compatibility with caching plugins and CDN services
- ✨ ENHANCEMENT: Improved Vue.js SPA integration with proper CORS handling
- ✨ ENHANCEMENT: Real-time custom field data display with hover-over information icon
- ✨ ENHANCEMENT: User-friendly field labels (auto-formatted from field keys)
- ✨ ENHANCEMENT: TypeScript support for custom field data structures

### Version 1.1.6
- 🚀 NEW: Bulk courier data update API endpoint (`/courier-data/update-bulk`) for efficient mass updates.
- 🚀 NEW: Bulk order status change API endpoint (`/orders/change-status-bulk`) for faster status management.
- ⚡ IMPROVED: Order status and courier data updates are now handled in bulk, reducing API calls and improving performance.
- ⚡ IMPROVED: Dashboard and analytics APIs now use consistent status filtering and support custom statuses.
- 🐛 FIX: Fixed logic for handling orders without consignment IDs during courier sync.
- 🐛 FIX: Minor bug fixes and code optimizations throughout the plugin.

### Version 1.1.5
- 🚀 NEW: Manual COD (Cash on Delivery) amount override when creating or updating orders, with automatic order note and audit trail.
- 🚀 NEW: Order note and metadata tracking for all manual COD modifications, including original and modified totals, user, and timestamp.
- 🚀 NEW: Improved sequential label printing—ensures each order label prints only after the previous one completes.
- 🚀 NEW: API endpoint for updating order total/COD now adds system note and metadata for all changes.
- 🚀 NEW: Helper function to retrieve COD modification notes for any order.
- ⚡ IMPROVED: Internet connection detection now prevents duplicate notifications and cleans up event listeners on component unmount.
- ⚡ IMPROVED: TypeScript and code structure for order creation, printing, and courier integration.
- ⚡ IMPROVED: Error handling and user notifications for all order and printing operations.
- 🐛 FIX: Prevented duplicate event listeners and notifications on SPA hot reloads.
- 🐛 FIX: Ensured order total update API validates input and handles errors gracefully.
- 🐛 FIX: Label printing now waits for print dialog to close before proceeding to the next order.
- 🐛 FIX: Various minor bug fixes and performance improvements throughout the plugin.

### Version 1.1.4
- 🚀 NEW: Abandoned cart tracking and recovery dashboard.
- 🚀 NEW: Product existence validation before creating orders from abandoned carts.
- 🚀 NEW: Admin bar menu with real-time order status counts.
- 🚀 NEW: Dropdown menu showing Call Not Received, Courier Entry, and New Orders counts.
- 🚀 NEW: Performance-optimized caching for order counts (2-minute cache).
- 🚀 NEW: Automatic cache clearing when orders are created or status changes.
- ⚡ IMPROVED: Order creation workflow with comprehensive product validation.
- ⚡ IMPROVED: Enhanced error messages for product availability issues.
- ⚡ IMPROVED: Partial order creation support (creates orders with available products only).
- ⚡ IMPROVED: Better TypeScript type definitions across all composables.
- ⚡ IMPROVED: User experience with detailed notifications for order operations.
- 🐛 FIX: 'Product not found' errors during abandoned cart order creation.
- 🐛 FIX: Invalid order status errors in abandoned order filtering.
- 🐛 FIX: Loading state conflicts in order status updates.
- 🐛 FIX: API response handling for different order creation response structures.
- ✨ ENHANCEMENT: Better phone number validation for Bangladesh numbers.
- ✨ ENHANCEMENT: Improved error logging and debugging for order operations.

## 📄 License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

## 📢 Support & Feedback

For support, feature requests, or bug reports, please [open an issue](https://github.com/your-repo/woo-easy-life/issues) or contact us via the plugin dashboard.

Thank you for using **WooEasyLife**!  
Empowering WooCommerce store owners with advanced automation, analytics, and operational
