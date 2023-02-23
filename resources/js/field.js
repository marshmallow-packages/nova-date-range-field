import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-nova-date-range-field', IndexField)
  app.component('detail-nova-date-range-field', DetailField)
  app.component('form-nova-date-range-field', FormField)
})
