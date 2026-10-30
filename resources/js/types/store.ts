export type StoreContact = {
    email: string;
    phone: string;
    instagram: string;
    address: string;
};

export type StoreTransfer = {
    bank: string;
    account_name: string;
    cuit: string;
    cbu: string;
    alias: string;
    discount_percent: number;
};

export type StoreSettings = {
    name: string;
    currency: string;
    free_shipping_from_cents: number;
    contact: StoreContact;
    transfer: StoreTransfer;
    mercadopago_enabled: boolean;
};

export type CategorySummary = {
    id: number;
    name: string;
    slug: string;
    description?: string | null;
    image_url?: string | null;
    is_active?: boolean;
    sort_order?: number;
    products_count?: number;
};

export type ProductImage = {
    id: number;
    path: string;
    url: string;
    alt: string | null;
    sort_order: number;
    is_primary: boolean;
};

export type ProductVariant = {
    id: number;
    product_id: number;
    size: string;
    color: string;
    color_hex: string | null;
    sku: string | null;
    stock: number;
    price_cents: number | null;
    effective_price_cents: number;
    is_active: boolean;
    label: string;
    product?: Product;
};

export type Product = {
    id: number;
    category_id: number | null;
    name: string;
    slug: string;
    short_description: string | null;
    description: string | null;
    price_cents: number;
    compare_at_price_cents: number | null;
    is_active: boolean;
    is_featured: boolean;
    primary_image_url: string | null;
    in_stock: boolean;
    images: ProductImage[];
    variants: ProductVariant[];
    variants_sum_stock?: number;
    category?: CategorySummary | null;
    created_at: string;
};

export type CartLine = {
    id: number;
    variant_id: number;
    quantity: number;
    stock: number;
    unit_price_cents: number;
    total_cents: number;
    size: string;
    color: string;
    color_hex: string | null;
    product_name: string;
    product_slug: string;
    product_image: string | null;
    available: boolean;
};

export type CartSummary = {
    items: CartLine[];
    count: number;
    subtotal_cents: number;
    free_shipping_from_cents: number;
};

export type ShippingZone = {
    id: number;
    name: string;
    provinces: string[] | null;
    cost_cents: number;
    free_from_cents: number | null;
    delivery_estimate: string | null;
    is_pickup: boolean;
    pickup_address: string | null;
    is_active: boolean;
    sort_order: number;
};

export type OrderItem = {
    id: number;
    product_id: number | null;
    product_variant_id: number | null;
    product_name: string;
    variant_size: string;
    variant_color: string;
    sku: string | null;
    unit_price_cents: number;
    quantity: number;
    total_cents: number;
};

export type OrderStatusHistory = {
    id: number;
    status: string;
    status_label: string;
    note: string | null;
    created_at: string;
    author?: {
        id: number;
        name: string;
    } | null;
};

export type Order = {
    id: number;
    user_id: number | null;
    number: string;
    tracking_code: string;
    status: string;
    status_label: string;
    payment_method: string;
    payment_method_label: string;
    payment_status: string;
    shipping_method: string;
    shipping_method_label: string;
    customer_first_name: string;
    customer_last_name: string;
    customer_email: string;
    customer_phone: string;
    customer_dni: string | null;
    shipping_zone_id: number | null;
    shipping_address: string | null;
    shipping_city: string | null;
    shipping_province: string | null;
    shipping_postal_code: string | null;
    subtotal_cents: number;
    shipping_cost_cents: number;
    discount_cents: number;
    total_cents: number;
    notes: string | null;
    paid_at: string | null;
    shipped_at: string | null;
    delivered_at: string | null;
    canceled_at: string | null;
    created_at: string;
    updated_at: string;
    items: OrderItem[];
    items_count?: number;
    status_histories: OrderStatusHistory[];
    shipping_zone?: ShippingZone | null;
    user?: {
        id: number;
        name: string;
        email: string;
    } | null;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    per_page: number;
};

export type AdminStats = {
    revenue_cents: number;
    orders_today: number;
    orders_pending: number;
    orders_total: number;
    products_active: number;
    low_stock_count: number;
};
