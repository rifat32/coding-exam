<template>
    <section>
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="">Product Name</label>
                            <input
                                type="text"
                                v-model="product_name"
                                placeholder="Product Name"
                                class="form-control"
                            />
                        </div>
                        <div class="form-group">
                            <label for="">Product SKU</label>
                            <input
                                type="text"
                                v-model="product_sku"
                                placeholder="Product Name"
                                class="form-control"
                            />
                        </div>
                        <div class="form-group">
                            <label for="">Description</label>
                            <textarea
                                v-model="description"
                                id=""
                                cols="30"
                                rows="4"
                                class="form-control"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4" v-if="type !== 'update'">
                    <div
                        class="card-header py-3 d-flex flex-row align-items-center justify-content-between"
                    >
                        <h6 class="m-0 font-weight-bold text-primary">Media</h6>
                    </div>
                    <div class="card-body border">
                        <vue-dropzone
                            ref="myVueDropzone"
                            id="dropzone"
                            :options="dropzoneOptions"
                        ></vue-dropzone>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div
                        class="card-header py-3 d-flex flex-row align-items-center justify-content-between"
                    >
                        <h6 class="m-0 font-weight-bold text-primary">
                            Variants
                        </h6>
                    </div>
                    <div class="card-body">
                        <div
                            class="row"
                            v-for="(item, index) in product_variant"
                        >
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Option</label>
                                    <select
                                        v-model="item.option"
                                        class="form-control"
                                    >
                                        <option
                                            v-for="variant in variants"
                                            :value="variant.id"
                                        >
                                            {{ variant.title }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label
                                        v-if="product_variant.length != 1"
                                        @click="
                                            product_variant.splice(index, 1);
                                            checkVariant;
                                        "
                                        class="float-right text-primary"
                                        style="cursor: pointer;"
                                        >Remove</label
                                    >
                                    <label v-else for="">.</label>
                                    <input-tag
                                        v-model="item.tags"
                                        @input="checkVariant"
                                        class="form-control"
                                    ></input-tag>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="card-footer"
                        v-if="
                            product_variant.length < variants.length &&
                                product_variant.length < 3
                        "
                    >
                        <button @click="newVariant" class="btn btn-primary">
                            Add another option
                        </button>
                    </div>

                    <div class="card-header text-uppercase">Preview</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td>Variant</td>
                                        <td>Price</td>
                                        <td>Stock</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="variant_price in product_variant_prices"
                                    >
                                        <td>{{ variant_price.title }}</td>
                                        <td>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="variant_price.price"
                                            />
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                class="form-control"
                                                v-model="variant_price.stock"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <h3 v-if="loading">
            Loading Please wait a moment. it will work ......
        </h3>
        <button
            @click="saveProduct"
            type="submit"
            class="btn btn-lg btn-primary"
        >
            Save
        </button>
    </section>
</template>

<script>
import vue2Dropzone from "vue2-dropzone";
import "vue2-dropzone/dist/vue2Dropzone.min.css";
import InputTag from "vue-input-tag";

export default {
    components: {
        vueDropzone: vue2Dropzone,
        InputTag
    },
    props: ["variants", "type", "product", "productvariantsprices"],

    data() {
        var self = this;
        return {
            loading: false,
            product_name: "",
            product_sku: "",
            description: "",
            images: [],
            product_id: null,
            product_variant: [
                {
                    option: this.variants[0].id,
                    tags: []
                }
            ],
            product_variant_prices: [],
            dropzoneOptions: {
                url: "/store-image",
                thumbnailWidth: 150,
                maxFilesize: 0.5,
                headers: {
                    "X-CSRF-TOKEN": document.head.querySelector(
                        "[name=csrf-token]"
                    ).content
                },
                success: (response, message) => {
                    this.images.push(message.image);

                    console.log(message);
                }
            }
        };
    },
    methods: {
        // it will push a new object into product variant
        newVariant() {
            let all_variants = this.variants.map(el => el.id);
            let selected_variants = this.product_variant.map(el => el.option);
            let available_variants = all_variants.filter(
                entry1 => !selected_variants.some(entry2 => entry1 == entry2)
            );
            // console.log(available_variants)

            this.product_variant.push({
                option: available_variants[0],
                tags: []
            });
        },

        // check the variant and render all the combination
        checkVariant() {
            let tags = [];
            this.product_variant_prices = [];
            this.product_variant.filter(item => {
                tags.push(item.tags);
            });

            this.getCombn(tags).forEach(item => {
                this.product_variant_prices.push({
                    title: item,
                    price: 0,
                    stock: 0
                });
            });
        },
        checkVariantUpdate(tags, price, stock) {
            this.product_variant_prices = [];
            this.product_variant.filter(item => {
                tags.push(item.tags);
            });
            console.log(tags, price, stock);
            //  return;
            let ab = [[tags]];
            this.getCombn(ab).forEach(item => {
                this.product_variant_prices.push({
                    title: item,
                    price: price,
                    stock: stock
                });
            });
        },

        // combination algorithm
        getCombn(arr, pre) {
            pre = pre || "";
            if (!arr.length) {
                return pre;
            }
            let self = this;
            let ans = arr[0].reduce(function(ans, value) {
                return ans.concat(
                    self.getCombn(arr.slice(1), pre + value + "/")
                );
            }, []);
            return ans;
        },

        // store product into database
        saveProduct() {
            this.loading = true;
            let product = {
                title: this.product_name,
                sku: this.product_sku,
                description: this.description,
                product_image: this.images,
                product_variant: this.product_variant,
                product_variant_prices: this.product_variant_prices
            };
            console.log(product);
            if (this.type && this.type == "update") {
                axios
                    .put(`/product/${this.product_id}`, product)
                    .then(response => {
                        console.log(response.data);
                        alert("data updated successfully");
                        this.loading = false;
                    })
                    .catch(error => {
                        alert("something went wrong");
                        console.log(error.response);
                        this.loading = false;
                    });

                return;
            }

            axios
                .post("/product", product)
                .then(response => {
                    console.log(response.data);
                    alert("data saved successfully");
                    this.product_name = "";
                    this.product_sku = "";
                    this.description = "";
                    this.images = [];
                    this.product_variant = [
                        {
                            option: this.variants[0].id,
                            tags: []
                        }
                    ];
                    this.product_variant_prices = [];
                    this.loading = false;
                })
                .catch(error => {
                    alert("something went wrong");
                    console.log(error.response);
                    this.loading = false;
                });

            console.log(product);
        }
    },
    mounted() {
        //  title: this.product_name,
        //  sku: this.product_sku,
        //  description: this.description,

        // product_variant: this.product_variant,
        //  product_variant_prices: this.product_variant_prices

        console.log("Component mounted.");
        if (this.type && this.type === "update") {
            console.log(this.product);
            this.product_id = this.product.id;
            this.product_name = this.product.title;
            this.product_sku = this.product.sku;
            this.description = this.product.description;

            console.log("vprices", this.productvariantsprices);
            let tags = [];
            let price, stock;

            this.productvariantsprices.map(el => {
                // aaaaaaaaaaaaaaaaaaaaaaaaaa
                price = el.price;
                stock = el.stock;

                if (el.product_variant_one) {
                    tags.push(el.product_variant_one.variant);
                    let variantIndex = this.product_variant.findIndex(el2 => {
                        if (
                            el.product_variant_one.variant_main.id == el2.option
                        ) {
                            return true;
                        }
                    });

                    if (variantIndex == -1) {
                        this.product_variant.push({
                            option: el.product_variant_one.variant_main.id,
                            tags: [el.product_variant_one.variant]
                        });
                    } else {
                        this.product_variant[variantIndex].tags = [
                            ...this.product_variant[variantIndex].tags,
                            el.product_variant_one.variant
                        ];
                    }
                }
                //aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                if (el.product_variant_two) {
                    tags.push(el.product_variant_two.variant);
                    let variantIndex = this.product_variant.findIndex(el2 => {
                        if (
                            el.product_variant_two.variant_main.id == el2.option
                        ) {
                            return true;
                        }
                    });

                    if (variantIndex == -1) {
                        this.product_variant.push({
                            option: el.product_variant_two.variant_main.id,
                            tags: [el.product_variant_two.variant]
                        });
                    } else {
                        this.product_variant[variantIndex].tags = [
                            ...this.product_variant[variantIndex].tags,
                            el.product_variant_two.variant
                        ];
                    }
                }
                //aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
                if (el.product_variant_three) {
                    tags.push(el.product_variant_three.variant);
                    let variantIndex = this.product_variant.findIndex(el2 => {
                        if (
                            el.product_variant_three.variant_main.id ==
                            el2.option
                        ) {
                            return true;
                        }
                    });

                    if (variantIndex == -1) {
                        this.product_variant.push({
                            option: el.product_variant_three.variant_main.id,
                            tags: [el.product_variant_three.variant]
                        });
                    } else {
                        this.product_variant[variantIndex].tags = [
                            ...this.product_variant[variantIndex].tags,
                            el.product_variant_three.variant
                        ];
                    }
                }

                console.log(el);
            });
            this.checkVariantUpdate(tags, price, stock);
        }
    }
};
</script>
