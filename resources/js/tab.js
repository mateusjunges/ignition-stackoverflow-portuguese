Ignition.registerTab((Vue) => {
    Vue.use(require('vue-moment'))
    Vue.component('ignition-stackoverflow-portuguese', require('./components/Tab'))
});
