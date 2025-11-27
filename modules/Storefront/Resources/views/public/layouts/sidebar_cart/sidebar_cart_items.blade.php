<template x-for="cartItem in $store.cart.items" :key="cartItem.id">
    <div x-data="CartItem(cartItem)" class="cart-item sidebar-cart-item">
        <a :href="productUrl" class="product-image">
            <img
                :src="baseImage"
                :class="{
                    'image-placeholder': !hasBaseImage,
                }"
                :alt="productName"
                loading="lazy"
            />
        </a>

        <div class="product-info">
            <a
                :href="productUrl"
                class="product-name"
                :title="productName"
                x-text="productName"
            >
            </a>
            
            <template x-if="hasAnyVariation">
                <ul class="list-inline product-options">
                    <template
                        x-for="(variation, key) in cartItem.variations"
                        :key="variation.id"
                    >
                        <li>
                            <label x-text="`${variation.name}:`"></label>
                            
                            <span x-text="`${variation.values[0].label}${variationsLength === Number(key) ? '' : ','}`"></span>
                        </li>
                    </template>
                </ul>
            </template>
            
            <template x-if="hasAnyOption">
                <ul class="list-inline product-options">
                    <template
                        x-for="(option, key) in cartItem.options"
                        :key="option.id"
                    >
                        <li>
                            <label x-text="`${option.name}:`"></label>
                            
                            <span x-text="`${optionValues(option)}${optionsLength === Number(key) ? '' : ','}`"></span>
                        </li>
                    </template>
                </ul>
            </template>

            <div class="product-info-bottom">
                <div class="line-summary" x-text="`${cartItem.product.unit_decimal ? Number(cartItem.qty).toFixed(2).replace(/\\.00$/, '') : cartItem.qty}${cartItem.product.unit_suffix ? ' ' + cartItem.product.unit_suffix : ''} x ${formatCurrency(unitPrice)} = ${formatCurrency(lineTotal(cartItem.qty))}`"></div>
            </div>
        </div>

        <div class="remove-cart-item">
            <button class="btn-remove" @click="removeCartItem">
                <i class="las la-times"></i>
            </button>
        </div>
    </div>
</template>
