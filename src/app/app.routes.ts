import { Routes } from '@angular/router';
import { Movimenti } from './components/movimenti/movimenti';
import { Deposito } from './components/deposito/deposito';
import { Prelievo } from './components/prelievo/prelievo';
import { Saldo } from './components/saldo/saldo';
import { Conversione } from './components/conversione/conversione';

export const routes: Routes = [
    {path: '', redirectTo: '/saldo', pathMatch: 'full'},
    {path: 'movimenti', component: Movimenti},
    {path: 'movimenti/:id', component: Movimenti},
    {path: 'deposito', component: Deposito},
    {path: 'prelievo', component: Prelievo},
    {path: 'saldo', component: Saldo},
    {path: 'conversione', component: Conversione},
];
