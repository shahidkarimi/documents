# Coding rules

## Eloquent Models vs DB Raw queries
Use as much as Eloquent Models / ORM. This will make the code clean readable as well as efficient.
For example there are two tables  for doctors and appointments viz 'doctors' and 'appointments'. We will have eloquent models for this in this way.
* Doctors *
```
class Doctor extends Eloquent{

}
```
* Appointments *
```
class Doctor extends Eloquent{

}
```
## Define ORM relationships precisely

### Doctor to Appointments relationship
```
class Doctor extends Eloquent{
   public function appointments()
   {
       return $this->hasMany(App\Models\Apointment::class, 'doctor_id');
   }
}
```
### Appointments to Doctor relationship
```
class Appointment extends Eloquent{
   public function appointments()
   {
       return $this->belongsTo(App\Models\Doctor::class);
   }
}
```
