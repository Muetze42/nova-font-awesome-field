import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-font-awesome-field', IndexField)
  app.component('detail-font-awesome-field', DetailField)
  app.component('form-font-awesome-field', FormField)
})
